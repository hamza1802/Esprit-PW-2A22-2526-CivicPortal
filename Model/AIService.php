<?php
/**
 * AIService.php
 * Lightweight AI assistant for the CivicPortal Service Requests module.
 *
 * Two public methods:
 *   - improveDescription(serviceType, description, requiredDocuments)
 *   - analyzeRequest(request, documents)
 *
 * Both methods always return a structured array (never throws to the controller).
 * If the API key is missing or the call fails, a graceful fallback is returned
 * so the UI never breaks.
 */

require_once __DIR__ . '/../Config/AIConfig.php';

class AIService
{
    /**
     * Improve a citizen-written request description AND review the documents
     * the citizen is about to attach. The AI never invents new documents:
     * it only assesses the fixed list provided by the form.
     *
     * @param string $serviceType        Selected service (e.g. "Birth Certificate")
     * @param string $description        Raw description typed by the citizen
     * @param array  $requiredDocuments  [{ label, provided, fileName?, type?, base64Data?, mimeType?, tooLarge? }, ...]
     * @return array {
     *     improvedDescription: string,
     *     issues:               string[],
     *     documentStatus:       [{ label, ok, comment }, ...],
     *     readyToSubmit:        bool,
     *     overallComment:       string,
     *     status:               'ok'|'fallback'|'error',
     *     message:              string
     * }
     */
    public static function improveDescription(
        string $serviceType,
        string $description,
        array $requiredDocuments = []
    ): array {
        $description = trim($description);
        $serviceType = trim($serviceType);

        $docs = [];
        $inlineFiles = [];
        foreach ($requiredDocuments as $d) {
            if (!is_array($d)) continue;
            $label    = (string)($d['label'] ?? '');
            $provided = !empty($d['provided']);
            $fileName = (string)($d['fileName'] ?? '');
            $type     = (string)($d['type'] ?? '');
            $b64      = (string)($d['base64Data'] ?? '');
            $mime     = (string)($d['mimeType']  ?? '');
            $tooLarge = !empty($d['tooLarge']);

            $docs[] = [
                'label'         => $label,
                'provided'      => $provided,
                'fileName'      => $fileName,
                'type'          => $type,
                'fileInspected' => $provided && $b64 !== '' && $mime !== '',
                'tooLarge'      => $tooLarge,
            ];
            if ($provided && $b64 !== '' && $mime !== '') {
                $inlineFiles[] = [
                    'label'    => $label,
                    'mimeType' => $mime,
                    'data'     => $b64,
                ];
            }
        }
        $docsForPrompt = empty($docs)
            ? "(no supporting document is required for this service)"
            : json_encode($docs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        if ($description === '' && empty(array_filter($docs, fn ($d) => $d['provided']))) {
            return [
                'improvedDescription' => '',
                'issues'              => ["Please write a description before asking the AI to improve it."],
                'documentStatus'      => array_map(static fn ($d) => [
                    'label'   => $d['label'],
                    'ok'      => false,
                    'comment' => 'Document missing.',
                ], $docs),
                'readyToSubmit'       => false,
                'overallComment'      => "The request is not ready to be submitted yet.",
                'status'              => 'fallback',
                'message'             => 'Description is empty.',
            ];
        }

        $systemPrompt = <<<TXT
You are an administrative assistant helping a citizen prepare a municipal
service request. You MUST reply in valid JSON only with this EXACT schema:

{
  "improvedDescription": string,
  "issues": string[],
  "documentStatus": [
    { "label": string, "ok": boolean, "comment": string }
  ],
  "readyToSubmit": boolean,
  "overallComment": string
}

Hard rules:
- Reply in the SAME language as the citizen's description.
- "improvedDescription": rewrite the citizen's text clearly and politely;
  keep the same intent; do NOT invent personal data.
- "issues": short bullet points if the description is empty, vague, missing
  context, etc. Empty array if none.
- "documentStatus": you MUST output one entry for EACH item of the provided
  "Required documents" list, in the SAME order, with the EXACT same "label".
  Do NOT add any extra documents.
  - When a document is missing (provided=false): "ok"=false, comment says it
    is required.
  - When a document is provided AND its file content is attached AFTER the
    text below (as inline PDF / image), you MUST INSPECT the file:
      * If the file CLEARLY corresponds to the expected document, set
        "ok"=true and briefly confirm in the comment.
      * If the file looks UNRELATED, blank, blurry, or is the wrong document
        type, set "ok"=false and tell the citizen to replace it.
      * If you cannot read the file with confidence, prefer "ok"=false and
        ask for a clearer scan.
  - When a document is provided but no inline content is available
    (fileInspected=false in the metadata), trust the filename and set
    "ok"=true with a neutral "Document attached." comment.
- "readyToSubmit": true ONLY if every document.ok is true AND the description
  is non-empty and clear. Otherwise false.
- "overallComment": one sentence. If readyToSubmit is true, congratulate the
  citizen and say the request is ready to send. Otherwise summarise what
  needs to be fixed.
- Do NOT add anything outside the JSON object.
TXT;

        $userPrompt = "Service type: " . ($serviceType ?: "(not specified)") . "\n"
            . "Citizen description:\n\"\"\"\n" . $description . "\n\"\"\"\n\n"
            . "Required documents (closed list - do not add others):\n"
            . $docsForPrompt
            . (empty($inlineFiles) ? "" : "\n\nInline file contents are attached below, one per provided document. Inspect each one before judging.");

        $response = self::callJsonModel($systemPrompt, $userPrompt, 'frontoffice', $inlineFiles);

        if ($response['status'] !== 'ok') {
            return [
                'improvedDescription' => $description,
                'issues'              => ["AI unavailable; original text preserved."],
                'documentStatus'      => array_map(static fn ($d) => [
                    'label'   => $d['label'],
                    'ok'      => $d['provided'],
                    'comment' => $d['provided'] ? 'Document attached.' : 'Document missing.',
                ], $docs),
                'readyToSubmit'       => false,
                'overallComment'      => "Cannot evaluate the request right now.",
                'status'              => $response['status'],
                'message'             => $response['message'],
            ];
        }

        $data = $response['data'];

        $aiStatusByLabel = [];
        foreach (($data['documentStatus'] ?? []) as $entry) {
            if (!is_array($entry)) continue;
            $label = (string)($entry['label'] ?? '');
            if ($label === '') continue;
            $aiStatusByLabel[$label] = [
                'ok'      => !empty($entry['ok']),
                'comment' => (string)($entry['comment'] ?? ''),
            ];
        }
        $documentStatus = [];
        foreach ($docs as $d) {
            $aiEntry = $aiStatusByLabel[$d['label']] ?? null;

            // Authoritative server-side truth: only trust AI's "ok=false" when
            // we actually sent the file content for inspection.
            if (!$d['provided']) {
                $ok = false;
            } elseif ($d['fileInspected'] && $aiEntry !== null) {
                $ok = (bool)$aiEntry['ok'];
            } else {
                $ok = true;
            }

            $defaultComment = $d['provided']
                ? ($d['tooLarge']
                    ? 'File attached (too large to inspect).'
                    : 'Document attached.')
                : 'Document missing.';

            $documentStatus[] = [
                'label'   => $d['label'],
                'ok'      => $ok,
                'comment' => $aiEntry['comment'] ?? $defaultComment,
            ];
        }

        $allDocsOk      = !empty($documentStatus)
            ? array_reduce($documentStatus, fn ($carry, $d) => $carry && $d['ok'], true)
            : true;
        $hasIssues      = !empty(self::stringList($data['issues'] ?? []));
        $aiReady        = !empty($data['readyToSubmit']);
        $readyToSubmit  = $aiReady && $allDocsOk && !$hasIssues;

        $overallComment = (string)($data['overallComment'] ?? '');
        if ($overallComment === '') {
            $overallComment = $readyToSubmit
                ? "Your request is ready to be submitted."
                : "Please fix the items above before submitting.";
        }

        return [
            'improvedDescription' => isset($data['improvedDescription']) ? (string)$data['improvedDescription'] : $description,
            'issues'              => self::stringList($data['issues'] ?? []),
            'documentStatus'      => $documentStatus,
            'readyToSubmit'       => $readyToSubmit,
            'overallComment'      => $overallComment,
            'status'              => 'ok',
            'message'             => 'AI suggestion ready.',
        ];
    }

    /**
     * Analyze a request for the worker dashboard.
     *
     * @param array $request    Full request row (id, title, description, status, ...)
     * @param array $documents  List of documents [{filePath, type, uploadedAt}, ...]
     * @return array
     */
    public static function analyzeRequest(array $request, array $documents = []): array
    {
        $title       = (string)($request['title'] ?? '');
        $description = (string)($request['description'] ?? '');
        $status      = (string)($request['status'] ?? 'pending');

        $uploadDir       = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');
        $totalBudget     = 6 * 1024 * 1024;
        $perFileMax      = 4 * 1024 * 1024;
        $remaining       = $totalBudget;
        $inlineFiles     = [];
        $docSummaryLines = [];

        foreach ($documents as $d) {
            $name = (string)($d['filePath'] ?? '');
            $type = (string)($d['type'] ?? 'other');
            if ($name === '') continue;

            $abs  = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $name;
            $mime = self::guessMime($name);
            $size = is_file($abs) ? (int)@filesize($abs) : 0;

            $inspected = false;
            if ($mime !== null && $size > 0 && $size <= $perFileMax && $size <= $remaining) {
                $bytes = @file_get_contents($abs);
                if ($bytes !== false && $bytes !== '') {
                    $inlineFiles[] = [
                        'label'    => "$type - $name",
                        'mimeType' => $mime,
                        'data'     => base64_encode($bytes),
                    ];
                    $remaining -= $size;
                    $inspected = true;
                }
            }
            $docSummaryLines[] = "- [$type] $name"
                . ($inspected ? " (inline content provided below)" : " (filename only - file not inspected)");
        }
        $docSummary = empty($docSummaryLines)
            ? "No documents attached."
            : implode("\n", $docSummaryLines);

        $systemPrompt = <<<TXT
You are a pragmatic administrative review assistant for municipal staff. The
worker is in charge - you only assist. Reply in valid JSON only with this
EXACT schema:
{
  "summary": string,
  "issues": string[],
  "validityScore": number,
  "recommendation": "approve" | "reject" | "request_more_info",
  "suggestedComment": string
}

Mindset:
- Be REALISTIC and pragmatic. Do NOT demand extra information that municipal
  workflows don't actually need. Only the "Service type" and the documents
  attached to this very request matter; if the citizen attached supporting
  documents and they look reasonable, that is enough.
- Default stance: APPROVE when:
  * The description matches the service type (or is at least clearly about it).
  * At least one supporting document is attached.
  * The attached files, when inspected, look like real, related documents.
- Recommend "request_more_info" ONLY when something specific is missing or
  unclear that the citizen could realistically provide (blurry scan,
  unrelated file, wrong person, expired document).
- Recommend "reject" ONLY for clear mismatches or fraud-looking content.
- Do NOT invent strict eligibility rules the city did not state.

Format rules:
- "summary": 1-2 neutral sentences describing what the citizen is asking.
- "issues": SHORT, FACTUAL bullet points (max 3) - only real problems.
- "validityScore": integer 0..100. Score generously: 80+ for "looks fine",
  60-79 for "minor doubt", 40-59 for "needs more info", below 40 only for
  clearly wrong submissions.
- "recommendation": "approve" | "reject" | "request_more_info" - see above.
- "suggestedComment": polite text the worker can paste back to the citizen.
- Reply in the SAME language as the citizen's description.
- Do NOT add anything outside the JSON object.

When inline documents are attached, INSPECT them. A document marked
"(inline content provided below)" is followed by its binary payload as the
next part of this prompt.
TXT;

        $userPrompt = "Service type: $title\n"
            . "Current status: $status\n"
            . "Citizen description:\n\"\"\"\n$description\n\"\"\"\n"
            . "Attached documents:\n$docSummary"
            . (empty($inlineFiles) ? "" : "\n\nFor each inline document below, the previous text line names which document it belongs to.");

        $response = self::callJsonModel($systemPrompt, $userPrompt, 'backoffice', $inlineFiles);

        if ($response['status'] !== 'ok') {
            return [
                'summary'          => "Automatic summary unavailable.",
                'issues'           => ["AI service unavailable."],
                'validityScore'    => 0,
                'recommendation'   => 'request_more_info',
                'suggestedComment' => "We need a moment to review your request manually. Thank you for your patience.",
                'status'           => $response['status'],
                'message'          => $response['message'],
            ];
        }

        $d = $response['data'];
        $rec = strtolower((string)($d['recommendation'] ?? 'request_more_info'));
        if (!in_array($rec, ['approve', 'reject', 'request_more_info'], true)) {
            $rec = 'request_more_info';
        }
        $score = (int)($d['validityScore'] ?? 0);
        $score = max(0, min(100, $score));

        return [
            'summary'          => (string)($d['summary'] ?? ''),
            'issues'           => self::stringList($d['issues'] ?? []),
            'validityScore'    => $score,
            'recommendation'   => $rec,
            'suggestedComment' => (string)($d['suggestedComment'] ?? ''),
            'status'           => 'ok',
            'message'          => 'AI analysis ready.',
        ];
    }

    // --------------------------------------------------------------------
    //  Internals
    // --------------------------------------------------------------------

    private static function callJsonModel(
        string $systemPrompt,
        string $userPrompt,
        string $context = 'frontoffice',
        array $inlineFiles = []
    ): array {
        try {
            $cfg = AIConfig::get($context);
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        if (empty($cfg['api_key'])) {
            return [
                'status'  => 'fallback',
                'message' => "AI key for '$context' is not configured. Set GEMINI_API_KEY_FRONT / GEMINI_API_KEY_BACK or edit Config/AIConfig.php.",
            ];
        }

        if ($cfg['provider'] === 'gemini') {
            return self::callGemini($cfg, $systemPrompt, $userPrompt, $inlineFiles);
        }
        return ['status' => 'error', 'message' => 'Unsupported AI provider.'];
    }

    private static function callGemini(
        array $cfg,
        string $systemPrompt,
        string $userPrompt,
        array $inlineFiles = []
    ): array {
        $endpoint = str_replace('{model}', $cfg['model'], $cfg['endpoint'])
            . '?key=' . urlencode($cfg['api_key']);

        $userParts = [['text' => $userPrompt]];
        foreach ($inlineFiles as $f) {
            $label = (string)($f['label'] ?? '');
            $mime  = (string)($f['mimeType'] ?? '');
            $data  = (string)($f['data'] ?? '');
            if ($mime === '' || $data === '') continue;
            $userParts[] = ['text' => "--- Begin attached document for: $label ---"];
            $userParts[] = ['inlineData' => ['mimeType' => $mime, 'data' => $data]];
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [[
                'role'  => 'user',
                'parts' => $userParts,
            ]],
            'generationConfig' => [
                'temperature'      => 0.4,
                'responseMimeType' => 'application/json',
                'thinkingConfig'   => ['thinkingBudget' => 0],
            ],
        ];

        $raw = self::httpPostJson($endpoint, $payload, [
            'Content-Type: application/json',
        ], $cfg['timeout']);

        if ($raw['status'] !== 'ok') return $raw;

        $body = $raw['body'];

        $finishReason = $body['candidates'][0]['finishReason'] ?? null;
        if ($finishReason && !in_array($finishReason, ['STOP', 'MAX_TOKENS'], true)) {
            return ['status' => 'error', 'message' => "Gemini stopped: $finishReason"];
        }
        if (isset($body['promptFeedback']['blockReason'])) {
            return ['status' => 'error', 'message' => 'Gemini blocked: ' . $body['promptFeedback']['blockReason']];
        }

        $parts = $body['candidates'][0]['content']['parts'] ?? [];
        $textChunks = [];
        foreach ($parts as $p) {
            if (!empty($p['thought'])) continue;
            if (isset($p['text']) && $p['text'] !== '') {
                $textChunks[] = $p['text'];
            }
        }
        $content = trim(implode("\n", $textChunks));

        if ($content === '') {
            return ['status' => 'error', 'message' => 'Gemini returned no content.'];
        }
        return self::decodeJsonContent($content);
    }

    private static function httpPostJson(string $url, array $payload, array $headers, int $timeout): array
    {
        if (!function_exists('curl_init')) {
            return ['status' => 'error', 'message' => 'cURL extension is not available on this server.'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $timeout,
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['status' => 'error', 'message' => 'AI HTTP error: ' . $err];
        }
        if ($code < 200 || $code >= 300) {
            return ['status' => 'error', 'message' => "AI HTTP $code: " . substr((string)$response, 0, 300)];
        }

        $body = json_decode($response, true);
        if (!is_array($body)) {
            return ['status' => 'error', 'message' => 'Invalid AI response.'];
        }
        return ['status' => 'ok', 'body' => $body, 'message' => 'OK'];
    }

    private static function decodeJsonContent(string $content): array
    {
        $trimmed = trim($content);

        if (strncmp($trimmed, '```', 3) === 0) {
            $trimmed = preg_replace('/^```(?:json)?\s*/i', '', $trimmed);
            $trimmed = preg_replace('/\s*```$/', '', $trimmed);
            $trimmed = trim($trimmed);
        }

        $data = json_decode($trimmed, true);

        if (!is_array($data)) {
            $start = strpos($trimmed, '{');
            $end   = strrpos($trimmed, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $candidate = substr($trimmed, $start, $end - $start + 1);
                $data = json_decode($candidate, true);
            }
        }

        if (!is_array($data)) {
            return ['status' => 'error', 'message' => 'Could not parse AI JSON.'];
        }
        return ['status' => 'ok', 'data' => $data, 'message' => 'OK'];
    }

    private static function guessMime(string $filename): ?string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $map = [
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            'heic' => 'image/heic',
            'heif' => 'image/heif',
        ];
        return $map[$ext] ?? null;
    }

    private static function stringList($value): array
    {
        if (!is_array($value)) return [];
        $out = [];
        foreach ($value as $v) {
            if (is_string($v) && trim($v) !== '') {
                $out[] = trim($v);
            }
        }
        return $out;
    }
}

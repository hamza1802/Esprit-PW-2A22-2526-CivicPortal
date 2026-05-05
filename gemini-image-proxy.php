<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Load .env
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (!$line || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

$apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'No Gemini API key configured']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$prompt = trim($input['prompt'] ?? '');
if (!$prompt) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt is required']);
    exit;
}

// --- Try Gemini 2.5 Flash Image first ---
$geminiUrl  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent?key=' . $apiKey;
$geminiBody = json_encode([
    'contents'         => [['parts' => [['text' => $prompt]]]],
    'generationConfig' => ['responseModalities' => ['IMAGE', 'TEXT']],
]);

$opts = ['http' => ['header' => "Content-Type: application/json\r\n", 'method' => 'POST', 'content' => $geminiBody, 'ignore_errors' => true, 'timeout' => 20]];
$geminiResponse = @file_get_contents($geminiUrl, false, stream_context_create($opts));

$b64  = null;
$mime = 'image/png';

if ($geminiResponse !== false) {
    preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
    $geminiStatus = (int)($match[1] ?? 500);

    if ($geminiStatus === 200) {
        $data  = json_decode($geminiResponse, true);
        $parts = $data['candidates'][0]['content']['parts'] ?? [];
        foreach ($parts as $part) {
            if (isset($part['inlineData'])) {
                $b64  = $part['inlineData']['data'];
                $mime = $part['inlineData']['mimeType'] ?? 'image/png';
                break;
            }
        }
    }
}

// --- Fallback: Pollinations.ai (free, no key required) ---
if (!$b64) {
    set_time_limit(90);
    $pollinationsUrl  = 'https://image.pollinations.ai/prompt/' . urlencode($prompt) . '?width=800&height=400&nologo=true&model=flux';
    $pollinationsOpts = ['http' => ['timeout' => 60, 'ignore_errors' => true]];
    $imgBinary = @file_get_contents($pollinationsUrl, false, stream_context_create($pollinationsOpts));
    if ($imgBinary !== false && strlen($imgBinary) > 1000) {
        $b64  = base64_encode($imgBinary);
        $mime = 'image/jpeg';
    }
}

if (!$b64) {
    http_response_code(502);
    echo json_encode(['error' => 'All image generation providers failed']);
    exit;
}

echo json_encode(['imageData' => $b64, 'mimeType' => $mime]);

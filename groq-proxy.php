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

// Collect all configured API keys
$apiKeys = [];
for ($i = 1; $i <= 10; $i++) {
    $key = $_ENV["GROQ_API_KEY_$i"] ?? null;
    if ($key) $apiKeys[] = $key;
}
// Fallback: legacy single-key env var
if (empty($apiKeys)) {
    $single = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY');
    if ($single) $apiKeys[] = $single;
}

if (empty($apiKeys)) {
    http_response_code(500);
    echo json_encode(['error' => 'No Groq API keys configured']);
    exit;
}

// Parse request
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

$prompt     = $input['prompt'] ?? '';
$system     = $input['system'] ?? 'You are a helpful AI assistant for CivicPortal.';
$maxTokens  = $input['max_tokens'] ?? 300;
$jsonMode   = $input['json_mode'] ?? false;
$temperature = $input['temperature'] ?? 0.7;

if (empty($prompt)) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt is required']);
    exit;
}

$url = 'https://api.groq.com/openai/v1/chat/completions';
$data = [
    'model'       => 'llama3-70b-8192',
    'messages'    => [
        ['role' => 'system', 'content' => $system],
        ['role' => 'user',   'content' => $prompt]
    ],
    'max_tokens'  => $maxTokens,
    'temperature' => $temperature
];
if ($jsonMode) {
    $data['response_format'] = ['type' => 'json_object'];
}

// Try each key; rotate on 429 (rate-limit) or 402/403 (quota exhausted)
$lastStatus   = 500;
$lastResponse = null;

foreach ($apiKeys as $apiKey) {
    $options = [
        'http' => [
            'header'        => "Content-type: application/json\r\nAuthorization: Bearer $apiKey\r\n",
            'method'        => 'POST',
            'content'       => json_encode($data),
            'ignore_errors' => true
        ]
    ];

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        $lastStatus   = 502;
        $lastResponse = json_encode(['error' => 'Failed to connect to Groq API']);
        continue;
    }

    preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
    $status = (int)($match[1] ?? 500);

    if ($status === 429 || $status === 402 || $status === 403) {
        // This key is exhausted/rate-limited — try the next one
        $lastStatus   = $status;
        $lastResponse = $response;
        continue;
    }

    // Success or a non-retryable error — return immediately
    http_response_code($status);
    echo $response;
    exit;
}

// All keys failed
http_response_code($lastStatus);
echo $lastResponse;

<?php
// Function to load the API key hash from .env
function getApiKeyHash() {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, 'API_KEY_HASH=') === 0) {
                return trim(substr($line, strlen('API_KEY_HASH=')));
            }
        }
    }
    return null;
}

// Function to validate API key
function isValidApiKey($incomingKey) {
    $storedHashedKey = getApiKeyHash();
    return $storedHashedKey && password_verify($incomingKey, $storedHashedKey);
}

// Function to validate API key and respond if invalid
function validateApiKey() {
    $headers = getallheaders();
    $incomingApiKey = $headers['x-api-key'] ?? null;

    if (!$incomingApiKey || !isValidApiKey($incomingApiKey)) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid API Key']);
        exit;
    }
}
?>

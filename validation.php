<?php
// Function to load the API key hash from .env
function getApiKeyHash() {
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        error_log('Environment file not found.');
        return null;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'API_KEY_HASH=') === 0) {
            return trim(substr($line, strlen('API_KEY_HASH=')));
        }
    }
    error_log('API_KEY_HASH not found in environment file.');
    return null;
}

// Function to validate API key
function isValidApiKey($incomingKey) {
    $storedHashedKey = getApiKeyHash();
    return $storedHashedKey && password_verify($incomingKey, $storedHashedKey);
}

// Function to retrieve incoming API key
function getIncomingApiKey() {
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        return $headers['x-api-key'] ?? null;
    }
    return $_SERVER['HTTP_X_API_KEY'] ?? null; // Fallback
}
 
// Function to validate API key and respond if invalid
function validateApiKey() {
    $incomingApiKey = getIncomingApiKey();

    if (!$incomingApiKey || strlen($incomingApiKey) < 20 || !isValidApiKey($incomingApiKey)) {
        error_log('Unauthorized access attempt detected.');
        $storedHashedKey = getApiKeyHash();
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized','key'=> $storedHashedKey]);
        exit;
    }
    
}
?>

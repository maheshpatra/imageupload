<?php
// Function to load the API key hash from .env file
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

// Directory for uploads
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Get API key from headers
$headers = getallheaders();
$incomingApiKey = $headers['x-api-key'] ?? null;

// Validate the API key
if (!$incomingApiKey || !isValidApiKey($incomingApiKey)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid API Key']);
    exit;
}

// Check if files are being uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $uploadedFiles = $_FILES['files'];
    $uploadResults = [];

    // Loop through each file
    for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
        $fileName = $uploadedFiles['name'][$i];
        $fileTmpName = $uploadedFiles['tmp_name'][$i];

        // Generate a unique name for each file using the current timestamp in milliseconds
        $newFileName = date('YmdHis') . '_' . round(microtime(true) * 1000) . '_' . preg_replace('/\s+/', '_', $fileName);
        $filePath = $uploadDir . $newFileName;

        // Attempt to move the uploaded file to the upload directory
        if (move_uploaded_file($fileTmpName, $filePath)) {
            $uploadResults[] = [
                'file' => $fileName,
                'status' => 'success',
                'url' => 'http://yourdomain.com/uploads/' . $newFileName // Update with actual domain
            ];
        } else {
            $uploadResults[] = [
                'file' => $fileName,
                'status' => 'failed',
                'message' => 'Failed to upload file.'
            ];
        }
    }

    // Return JSON response with upload results
    echo json_encode(['status' => 'success', 'uploads' => $uploadResults]);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No files uploaded or invalid request.']);
}
?>

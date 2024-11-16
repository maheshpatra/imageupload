<?php
header('Content-Type: application/json');

// Function to generate the hash
function generateHash($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Function to update the .env file
function updateEnvFile($hash) {
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        return ['success' => false, 'message' => 'Environment file not found.'];
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updatedLines = [];
    $hashUpdated = false;

    foreach ($lines as $line) {
        if (strpos($line, 'API_KEY_HASH=') === 0) {
            $updatedLines[] = "API_KEY_HASH={$hash}";
            $hashUpdated = true;
        } else {
            $updatedLines[] = $line;
        }
    }

    // If API_KEY_HASH doesn't exist, add it to the file
    if (!$hashUpdated) {
        $updatedLines[] = "API_KEY_HASH={$hash}";
    }

    // Write the updated lines back to the file
    file_put_contents($envFile, implode(PHP_EOL, $updatedLines));
    return ['success' => true, 'message' => 'API_KEY_HASH successfully updated.'];
}

// Handle the API request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $password = $input['password'] ?? null;

    if (!$password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password is required.']);
        exit;
    }

    $hash = generateHash($password);
    $result = updateEnvFile($hash);

    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => $result['message'], 'hash' => $hash]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed.']);
}
?>

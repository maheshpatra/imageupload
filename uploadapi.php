<?php
// Include validation logic
require_once __DIR__ . '/validation.php';

// Validate API key before proceeding
validateApiKey();

// Directory Setup for File Uploads
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = preg_replace('/\s+/', '_', basename($file['name']));
    $fileTmpName = $file['tmp_name'];
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($fileTmpName, $filePath)) {
        $fileUrl = "https://files.finafid.org/uploads/" . $fileName;
        echo json_encode(['status' => 'success', 'url' => $fileUrl]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload file.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or invalid request.']);
}
?>

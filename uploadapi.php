<?php
require_once __DIR__ . '/validation.php';
header("Access-Control-Allow-Origin: http://127.0.0.1:3000"); // Adjust the URL if needed




validateApiKey();

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['images'])) {
    $responses = [];

    foreach ($_FILES['images']['name'] as $index => $originalName) {
        $fileTmpName = $_FILES['images']['tmp_name'][$index];
        $fileType = $_FILES['images']['type'][$index];
        $fileSize = $_FILES['images']['size'][$index];
        $fileError = $_FILES['images']['error'][$index];

        // Skip files with errors
        if ($fileError !== UPLOAD_ERR_OK) {
            $responses[] = ['status' => 'error', 'message' => 'File upload error.'];
            continue;
        }

        // Validate MIME type
        if (!in_array($fileType, $allowedMimeTypes)) {
            $responses[] = ['status' => 'error', 'message' => 'Invalid file type.'];
            continue;
        }

        // Process file upload
        $timestamp = date("YmdHis");
        $fileName = $timestamp . '_' . preg_replace('/\s+/', '_', basename($originalName));
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpName, $filePath)) {
            $fileUrl = "https://files.finafid.org/uploads/" . $fileName;
            $responses[] = ['status' => 'success', 'url' => $fileUrl];
        } else {
            $responses[] = ['status' => 'error', 'message' => 'Failed to upload file.'];
        }
    }

    echo json_encode($responses);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No files uploaded or invalid request.']);
}
?>

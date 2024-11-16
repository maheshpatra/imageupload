<?php
require_once __DIR__ . '/validation.php';

validateApiKey();

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // Debugging logs
    error_log("Uploaded file details: " . print_r($file, true));

    if (!in_array($file['type'], $allowedMimeTypes)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Only image files (JPG, PNG, GIF) are allowed.']);
    }

    print_r($timestamp = date("YmdHis"));
    $fileName = $timestamp . '_' . preg_replace('/\s+/', '_', basename($file['name']));
    $fileTmpName = $file['tmp_name'];
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($fileTmpName, $filePath)) {
        $fileUrl = "https://files.finafid.org/uploads/" . $fileName;
        echo json_encode(['status' => 'success', 'url' => $fileUrl]);
    } else {
        error_log("Failed to move uploaded file.");
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
    }
} else {
    error_log("No file uploaded or invalid request.");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or invalid request.']);
}
?>

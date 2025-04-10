<?php
// Include validation logic
require_once __DIR__ . '/../validation.php';

validateApiKey();
$invoiceDir = __DIR__ . '/uploads/';
if (!is_dir($invoiceDir)) {
    mkdir($invoiceDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Generate a unique name using date and time
    $timestamp = date('Ymd_His'); // Format: YYYYMMDD_HHMMSS
    $uniqueName = $timestamp . '_' . uniqid() . '.pdf';

    $fileTmpName = $file['tmp_name'];
    $filePath = $invoiceDir . $uniqueName;

    // Ensure the file is a PDF
    $fileType = mime_content_type($fileTmpName);
    if ($fileType !== 'application/pdf') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Only PDF files are allowed.']);
        exit;
    }

    // Move the uploaded file
    if (move_uploaded_file($fileTmpName, $filePath)) {
        $fileUrl = "https://files.finafid.org/invoice/uploads/" . $uniqueName;
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

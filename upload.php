<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

$uploadsDir = 'files/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4'];
$uploadStatus = '';

if (!empty($_FILES['files']['name'][0])) {
    foreach ($_FILES['files']['name'] as $index => $name) {
        $tmpName = $_FILES['files']['tmp_name'][$index];
        $fileType = $_FILES['files']['type'][$index];

        // Ensure file type is allowed
        if (!in_array($fileType, $allowedTypes)) {
            $uploadStatus = 'Failed to upload. Unsupported file type.';
            break;
        }

        // Remove spaces and sanitize file name
        $name = str_replace(' ', '_', basename($name));

        // Generate a unique filename based on current date and time with milliseconds
        $timestamp = microtime(true); // Get current Unix timestamp with microseconds
        $dateTime = date('Ymd_His', floor($timestamp)); // Get date and time up to seconds
        $milliseconds = round(($timestamp - floor($timestamp)) * 1000); // Get milliseconds
        $fileExtension = pathinfo($name, PATHINFO_EXTENSION); // Get file extension
        $uniqueName = $dateTime . '_' . $milliseconds . '.' . $fileExtension; // Combine everything into a unique filename

        // Define target file path
        $targetFile = $uploadsDir . $uniqueName;

        // If file exists, try again with a new name (although this is rare with the timestamp)
        while (file_exists($targetFile)) {
            $milliseconds = rand(100, 999); // Retry with a random millisecond if collision happens
            $uniqueName = $dateTime . '_' . $milliseconds . '.' . $fileExtension;
            $targetFile = $uploadsDir . $uniqueName;
        }

        // Move uploaded file to target directory
        if (move_uploaded_file($tmpName, $targetFile)) {
            $uploadStatus = 'File uploaded successfully!';
        } else {
            $uploadStatus = 'Failed to upload file.';
        }
    }
} else {
    $uploadStatus = 'No files selected.';
}

// Redirect back to the dashboard with status message
header("Location: dashboard.php?status=" . urlencode($uploadStatus));
exit;
?>

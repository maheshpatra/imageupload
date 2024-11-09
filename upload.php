<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadsDir = 'uploads/';
    $fileName = basename($_FILES['file']['name']);
    $targetFilePath = $uploadsDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
        header('Location: dashboard.php');
    } else {
        echo "Error uploading file.";
    }
}
?>

<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

$uploadsDir = 'uploads/';
$uploadedFiles = array_diff(scandir($uploadsDir), array('.', '..'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <h2>Welcome, Admin!</h2>
        
        <div class="section">
            <h3>Upload Image or Video</h3>
            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="file" accept="image/*,video/*" required>
                <button type="submit">Upload</button>
            </form>
        </div>

        <div class="section">
            <h3>Recently Uploaded Files</h3>
            <div class="uploads-list">
                <?php foreach ($uploadedFiles as $file): ?>
                    <div class="file-item">
                        <a href="<?php echo $uploadsDir . $file; ?>" target="_blank"><?php echo $file; ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>

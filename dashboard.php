<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

$uploadsDir = 'files/';
$uploadedFiles = array_diff(scandir($uploadsDir), array('.', '..')); // Get all files
$baseUrl = "https://files.finafid.org/files/";

// Get current timestamp with milliseconds
$currentTimestamp = microtime(true);

// Function to extract timestamp from the filename (which includes the timestamp)
function extractTimestamp($filename) {
    $nameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
    $timestamp = substr($nameWithoutExtension, 0, 14); // Extract date and time part of filename (YYYYMMDD_HHMMSS)
    return $timestamp;
}

// Sort files based on how close their timestamp is to the current timestamp
usort($uploadedFiles, function($a, $b) use ($currentTimestamp) {
    $timestampA = extractTimestamp($a);
    $timestampB = extractTimestamp($b);

    // Convert timestamps to seconds with microseconds
    $timestampA = strtotime($timestampA) + (float)substr($timestampA, 15) / 1000;
    $timestampB = strtotime($timestampB) + (float)substr($timestampB, 15) / 1000;

    return ($timestampB - $currentTimestamp) <=> ($timestampA - $currentTimestamp);
});

// Pagination settings
$filesPerPage = 24;
$totalFiles = count($uploadedFiles);
$totalPages = ceil($totalFiles / $filesPerPage);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));

$offset = ($currentPage - 1) * $filesPerPage;
$filesToShow = array_slice($uploadedFiles, $offset, $filesPerPage);

// Get the upload status message
$uploadStatus = isset($_GET['status']) ? $_GET['status'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Image</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function copyUrl(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert("URL copied to clipboard!");
            }).catch(err => {
                console.error("Error copying URL: ", err);
            });
        }

        function showLoading() {
            document.getElementById('loading-indicator').style.display = 'flex';
        }

        window.onload = function() {
            const status = '<?php echo $uploadStatus; ?>';
            if (status) {
                const popup = document.getElementById('upload-status-popup');
                const message = document.getElementById('popup-message');
                const popupClass = status.includes('successfully') ? 'success' : 'error';

                popup.classList.add(popupClass);
                message.textContent = status;
                popup.style.display = 'block';

                // Hide the popup after 3 seconds
                setTimeout(() => {
                    popup.style.display = 'none';
                }, 3000);
            }
        }
    </script>
</head>
<body>
    <!-- Success/Error Popup -->
    <div id="upload-status-popup" class="upload-status-popup">
        <p id="popup-message"></p>
    </div>

    <div id="loading-indicator" class="loading-indicator">
        <div class="spinner"></div>
        <p>Uploading...</p>
    </div>

    <div class="dashboard">
        <h2>Welcome, Admin!</h2>

        <div class="section">
            <h3>Upload Images or Videos</h3>
            <form action="upload.php" method="POST" enctype="multipart/form-data" onsubmit="showLoading()">
                <input type="file" name="files[]" accept="image/*,video/*" multiple required>
                <button type="submit">Upload</button>
            </form>
        </div>

        <div class="section">
            <h3>Recently Uploaded Files</h3>

            <div class="uploads-grid">
                <?php foreach ($filesToShow as $file): ?>
                    <div class="file-item">
                        <a href="<?php echo $uploadsDir . $file; ?>" target="_blank">
                            <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)): ?>
                                <img src="<?php echo $uploadsDir . $file; ?>" alt="<?php echo $file; ?>" class="thumbnail">
                            <?php else: ?>
                                <span class="file-placeholder"><?php echo $file; ?></span>
                            <?php endif; ?>
                        </a>
                        <button onclick="copyUrl('<?php echo $baseUrl . urlencode($file); ?>')">Copy URL</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination Controls -->
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination-link">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-link">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

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
    // Defensive parse for microseconds after 14 chars, fallback 0 if missing
    $microA = (float)(strlen($timestampA) > 14 ? substr($timestampA, 14) : 0);
    $microB = (float)(strlen($timestampB) > 14 ? substr($timestampB, 14) : 0);

    $timeA = strtotime($timestampA) + $microA / 1000;
    $timeB = strtotime($timestampB) + $microB / 1000;

    return ($timeB - $currentTimestamp) <=> ($timeA - $currentTimestamp);
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Image</title>
    <link rel="stylesheet" href="style.css" />
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
    <style>
        /* Simple styles for pagination and file grid */
        .pagination {
            margin: 20px 0;
            font-family: Arial, sans-serif;
        }
        .pagination-link, .pagination-ellipsis {
            display: inline-block;
            padding: 6px 12px;
            margin-right: 4px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            color: #333;
            background-color: #f0f0f0;
        }
        .pagination-link:hover {
            background-color: #ddd;
        }
        .pagination-link.active {
            background-color: #007BFF;
            color: white;
            font-weight: bold;
            pointer-events: none;
            cursor: default;
        }
        .pagination-ellipsis {
            cursor: default;
            color: #888;
            background-color: transparent;
            padding: 6px 10px;
        }
        .uploads-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .file-item {
            width: 150px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 8px;
        }
        .thumbnail {
            max-width: 100%;
            border-radius: 4px;
        }
        .file-placeholder {
            font-size: 14px;
            color: #555;
            display: block;
            padding: 40px 0;
            background-color: #efefef;
            border-radius: 4px;
        }
        /* Popup styles */
        .upload-status-popup {
            display: none;
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            z-index: 1000;
        }
        .upload-status-popup.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .upload-status-popup.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        /* Loading indicator */
        .loading-indicator {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 1100;
            font-family: Arial, sans-serif;
        }
        .spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Success/Error Popup -->
    <div id="upload-status-popup" class="upload-status-popup">
        <p id="popup-message"></p>
    </div>

    <!-- Loading Indicator -->
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
                                <img src="<?php echo $uploadsDir . $file; ?>" alt="<?php echo htmlspecialchars($file); ?>" class="thumbnail" />
                            <?php else: ?>
                                <span class="file-placeholder"><?php echo htmlspecialchars($file); ?></span>
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

                <?php
                function getCompactPages($current, $total) {
                    $pages = [];

                    if ($total <= 7) {
                        for ($i = 1; $i <= $total; $i++) {
                            $pages[] = $i;
                        }
                    } else {
                        $pages[] = 1;

                        if ($current > 4) {
                            $pages[] = '...';
                        }

                        $start = max(2, $current - 2);
                        $end = min($total - 1, $current + 2);

                        for ($i = $start; $i <= $end; $i++) {
                            $pages[] = $i;
                        }

                        if ($current < $total - 3) {
                            $pages[] = '...';
                        }

                        $pages[] = $total;
                    }

                    return $pages;
                }

                $compactPages = getCompactPages($currentPage, $totalPages);

                foreach ($compactPages as $page):
                    if ($page === '...'): ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php elseif ($page == $currentPage): ?>
                        <span class="pagination-link active"><?php echo $page; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $page; ?>" class="pagination-link"><?php echo $page; ?></a>
                <?php endif; endforeach; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-link">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

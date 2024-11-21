<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

// FTP server details
$ftp_server = "localhost:21"; // e.g., "ftp.example.com"
$ftp_username = "zenith"; // FTP username
$ftp_password = "your_password"; // FTP password
$uploadDir = 'uploads';
$thumbnailDir = 'uploads/thumbnails';

// Function to log errors
function logError($message) {
    $logFile = 'upload_errors.log'; // Log file path
    $currentDateTime = date('Y-m-d H:i:s');
    $logMessage = "[$currentDateTime] ERROR: $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Connect to FTP server
$conn_id = ftp_connect($ftp_server);
if (!$conn_id) {
    logError("Couldn't connect to FTP server: $ftp_server. Check if the hostname is correct and reachable.");
    die("Couldn't connect to FTP server: $ftp_server.");
}

if (!ftp_login($conn_id, $ftp_username, $ftp_password)) {
    logError("Couldn't connect as $ftp_username");
    die("Couldn't connect as $ftp_username");
}
// Create directories if they don't exist
if (!ftp_nlist($conn_id, $uploadDir)) {
    ftp_mkdir($conn_id, $uploadDir);
}
if (!ftp_nlist($conn_id, $thumbnailDir)) {
    ftp_mkdir($conn_id, $thumbnailDir);
}

$title = $_POST['title'];
$description = $_POST['description'];

// Insert video details into the database to get the unique ID
$sql = "INSERT INTO videos (title, description, thumbnail, video_path) VALUES (?, ?, '', '')";
$stmt = $connection->prepare($sql);
$stmt->bind_param("ss", $title, $description);
$stmt->execute();

// Check for errors
if ($stmt->error) {
    logError("Database insertion failed: " . $stmt->error);
    die("Database insertion failed: " . $stmt->error);
}

// Get the last inserted ID
$lastId = $connection->insert_id;

// Generate a unique base name for both video and thumbnail using the ID
$uniqueBaseName = $uploadDir . '/' . $lastId;

// Handle video upload
$videoPath = $uniqueBaseName . '.mp4';
if (!ftp_put($conn_id, $videoPath, $_FILES['video']['tmp_name'], FTP_BINARY)) {
    logError("Video upload failed via FTP.");
    http_response_code(400);
    die("Video upload failed via FTP.");
}

// Handle thumbnail upload
$thumbnailTempPath = $_FILES['thumbnail']['tmp_name'];
$webpThumbnailPath = $thumbnailDir . '/' . $lastId . '.webp';

// Resize and convert thumbnail to WebP format
$thumbnailImage = imagecreatefromstring(file_get_contents($thumbnailTempPath));
if ($thumbnailImage !== false) {
    // Resize the image to a smaller size (adjust dimensions as needed)
    $width = 200;  // New width
    $height = 200; // New height
    $resizedImage = imagescale($thumbnailImage, $width, $height);

    // Save the resized image as a WebP file locally
    if (imagewebp($resizedImage, $webpThumbnailPath, 80)) {
        // Upload the WebP thumbnail to the FTP server
        if (!ftp_put($conn_id, $webpThumbnailPath, $webpThumbnailPath, FTP_BINARY)) {
            logError("Thumbnail upload failed via FTP.");
            http_response_code(400);
            die("Thumbnail upload failed via FTP.");
        }

        // Free up memory
        imagedestroy($thumbnailImage);
        imagedestroy($resizedImage);

        // Update the video record with paths for video and thumbnail
        $sql = "UPDATE videos SET thumbnail = ?, video_path = ? WHERE id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssi", $webpThumbnailPath, $videoPath, $lastId);
        $stmt->execute();
    } else {
        logError("Thumbnail upload failed during image saving.");
        http_response_code(400);
        die("Thumbnail upload failed during image saving.");
    }
} else {
    logError("Thumbnail upload failed. Could not process image.");
    http_response_code(400);
    die("Thumbnail upload failed. Could not process image.");
}

// Close the FTP connection
ftp_close($conn_id);

// Close the database connection
$connection->close();

http_response_code(200);
echo "Video and thumbnail uploaded successfully!";
?>
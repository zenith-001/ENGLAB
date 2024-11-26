<?php
session_start(); // Start the session to store progress

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php'; // Include your database connection file

// FTP server details
$ftp_server = "localhost"; // e.g., "ftp.example.com"
$ftp_username = "zenith"; // FTP username
$ftp_password = "8038@Zenith"; // FTP password
$uploadDir = 'uploads';
$thumbnailDir = 'uploads/thumbnails';

// Function to log errors
function logError($message)
{
    $logFile = 'upload_errors.log'; // Log file path
    $currentDateTime = date('Y-m-d H:i:s');
    $logMessage = "[$currentDateTime] ERROR: $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to update progress
function updateProgress($percent)
{
    $_SESSION['upload_progress'] = $percent; // Store the percentage directly
    logError("Upload progress: " . $percent . "%"); // Log as percentage
}
// Connect to FTP server
$conn_id = ftp_connect($ftp_server);
if (!$conn_id) {
    logError("Couldn't connect to FTP server: $ftp_server.");
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

// Get video details from POST request
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';

// Insert video details into the database to get the unique ID
$sql = "INSERT INTO videos (title, description) VALUES (?, ?)";
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

// Handle video upload
$videoPath = $uploadDir . '/' . $lastId . '.mp4';
$fileSize = $_FILES['video']['size'];
$localFile = $_FILES['video']['tmp_name'];
$handle = fopen($localFile, 'rb');

if (!$handle) {
    logError("Failed to open local file for reading.");
    die("Failed to open local file for reading.");
}
$uploadedSize = 0;
$handle = fopen($localFile, 'rb');

if (!$handle) {
    logError("Failed to open local file for reading.");
    die("Failed to open local file for reading.");
}

while (!feof($handle)) {
    $buffer = fread($handle, 8192); // Read in chunks
    $bufferSize = strlen($buffer);
    $uploadedSize += $bufferSize;

    // Use a temporary file to write the buffer
    $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
    file_put_contents($tempFile, $buffer);

    // Upload the temporary file
    if (!ftp_put($conn_id, $videoPath, $tempFile, FTP_BINARY)) {
        logError("Video upload failed via FTP.");
        fclose($handle);
        unlink($tempFile); // Clean up the temporary file
        die("Video upload failed via FTP.");
    }

    // Update progress
    updateProgress(($uploadedSize / $fileSize) * 100); // Update progress
    unlink($tempFile); // Clean up the temporary file
}

fclose($handle);

// Handle thumbnail upload
$thumbnailTempPath = $_FILES['thumbnail']['tmp_name'];
$jpegThumbnailPath = $thumbnailDir . '/' . $lastId . '.jpg';

// Check if the uploaded file is a valid image
if (exif_imagetype($thumbnailTempPath) === false) {
    logError("Uploaded file is not a valid image.");
    http_response_code(400);
    die("Uploaded file is not a valid image.");
}

// Move the uploaded file to the thumbnail directory
if (!move_uploaded_file($thumbnailTempPath, $jpegThumbnailPath)) {
    logError("Thumbnail upload failed during file move.");
    http_response_code(400);
    die("Thumbnail upload failed during file move.");
}

// Update the video record with paths for video and thumbnail
$sql = "UPDATE videos SET thumbnail = ?, video_path = ? WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("ssi", $jpegThumbnailPath, $videoPath, $lastId);
$stmt->execute();

// Close the FTP connection
ftp_close($conn_id);

// Close the database connection
$connection->close();

// Return success response
http_response_code(200);
echo json_encode(["message" => "Upload successful", "video_path" => $videoPath, "thumbnail" => $jpegThumbnailPath]);
?>
<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $thumbnail = $_FILES['thumbnail'];
    $video = $_FILES['video'];

    // Validate inputs
    if (empty($title) || empty($description) || $thumbnail['error'] !== UPLOAD_ERR_OK || $video['error'] !== UPLOAD_ERR_OK) {
        die("Please fill in all fields and upload valid files.");
    }

    // Define upload directories
    $thumbnailDir = 'uploads/thumbnails/';
    $videoDir = 'uploads/videos/';

    // Create directories if they don't exist
    if (!is_dir($thumbnailDir)) {
        mkdir($thumbnailDir, 0755, true);
    }
    if (!is_dir($videoDir)) {
        mkdir($videoDir, 0755, true);
    }

    // Move uploaded files
    $thumbnailPath = $thumbnailDir . basename($thumbnail['name']);
    $videoPath = $videoDir . basename($video['name']);

    if (move_uploaded_file($thumbnail['tmp_name'], $thumbnailPath) && move_uploaded_file($video['tmp_name'], $videoPath)) {
        // Prepare SQL statement
        $stmt = $connection->prepare("INSERT INTO videos (title, description, thumbnail, video_path, upload_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $title, $description, $thumbnailPath, $videoPath);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Upload successful!";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error uploading files.";
    }
}

// Close database connection
$connection->close();
?>
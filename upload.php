<?php
include 'db.php';

// FTP configuration
$ftp_server = "localhost:21"; // Replace with your FTP server address
$ftp_username = "zenith"; // Replace with your FTP username
$ftp_password = "8038@Zenith"; // Replace with your FTP password

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $thumbnail = $_FILES['thumbnail'];
    $video = $_FILES['video'];

    // Validate inputs
    if (empty($title) || empty($description) || $thumbnail['error'] !== UPLOAD_ERR_OK || $video['error'] !== UPLOAD_ERR_OK) {
        die("Please fill in all fields and upload valid files.");
    }

    // Prepare SQL statement to insert the video record without file paths
    $stmt = $connection->prepare("INSERT INTO videos (title, description, upload_date) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $title, $description);

    // Execute the statement
    if ($stmt->execute()) {
        // Get the last inserted ID
        $last_id = $stmt->insert_id;

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

        // Move uploaded video to local server
        $videoPath = $videoDir . $last_id . '.' . pathinfo($video['name'], PATHINFO_EXTENSION); // Retain original video extension

        if (move_uploaded_file($video['tmp_name'], $videoPath)) {
            // Convert uploaded image to WebP format
            $thumbnailPath = $thumbnailDir . $last_id . '.webp'; // Save thumbnail as WebP

            // Load the image
            $image = imagecreatefromstring(file_get_contents($thumbnail['tmp_name']));
            if ($image !== false) {
                // Save the image as WebP
                imagewebp($image, $thumbnailPath, 100); // 100 for maximum quality
                imagedestroy($image); // Free memory

                // Update the database with the file paths
                $stmt = $connection->prepare("UPDATE videos SET thumbnail = ?, video_path = ? WHERE id = ?");
                $stmt->bind_param("ssi", $thumbnailPath, $videoPath, $last_id);
                $stmt->execute();

                echo "Upload successful!";
            } else {
                echo "Error processing image.";
            }
        } else {
            echo "Error uploading video.";
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Close database connection
$connection->close();
?>
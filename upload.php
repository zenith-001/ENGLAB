<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

// Check if necessary directories exist, if not, create them
$uploadDir = 'uploads';
$thumbnailDir = 'uploads/thumbnails';

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
    http_response_code(500);
    die("Failed to create uploads directory.");
}
if (!is_dir($thumbnailDir) && !mkdir($thumbnailDir, 0777, true)) {
    http_response_code(500);
    die("Failed to create thumbnails directory.");
}

$title = $_POST['title'];
$description = $_POST['description'];

// Insert video details into the database to get the unique ID
try {
    $sql = "INSERT INTO videos (title, description, thumbnail, video_path) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, '', '']); // Use '' as a placeholder for both thumbnail and video_path
    

    // Get the last inserted ID
    $lastId = $pdo->lastInsertId();
} catch (PDOException $e) {
    http_response_code(500);
    die("Database insertion failed: " . $e->getMessage());
}

// Generate a unique base name for both video and thumbnail using the ID
$uniqueBaseName = $uploadDir . '/' . $lastId;

// Handle video upload
$videoPath = $uniqueBaseName . '.mp4';
if (!move_uploaded_file($_FILES['video']['tmp_name'], $videoPath)) {
    http_response_code(400);
    die("Video upload failed. Error moving file to $videoPath");
}

// Handle thumbnail upload and conversion to WebP
$thumbnailTempPath = $_FILES['thumbnail']['tmp_name'];
$thumbnailImage = imagecreatefromstring(file_get_contents($thumbnailTempPath));

if ($thumbnailImage !== false) {
    // Set the path for the compressed WebP thumbnail using the ID
    $webpThumbnailPath = $thumbnailDir . '/' . $lastId . '.webp';

    // Resize the image to a smaller size (adjust dimensions as needed)
    $width = 200;  // New width
    $height = 200; // New height
    $resizedImage = imagescale($thumbnailImage, $width, $height);

    // Save the resized image as a WebP file
    if (imagewebp($resizedImage, $webpThumbnailPath, 80)) { // 80 is the quality (0-100)
        // Free up memory
        imagedestroy($thumbnailImage);
        imagedestroy($resizedImage);

        // Update the video record with paths for video and thumbnail
        $sql = "UPDATE videos SET thumbnail = ?, video_path = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$webpThumbnailPath, $videoPath, $lastId]);
    } else {
        // Handle error if saving WebP file fails
        http_response_code(400);
        die("Thumbnail upload failed during image saving.");
    }
} else {
    // Handle error - thumbnail image creation failed
    http_response_code(400);
    die("Thumbnail upload failed. Could not process image.");
}

http_response_code(200);
echo "Video and thumbnail uploaded successfully!";

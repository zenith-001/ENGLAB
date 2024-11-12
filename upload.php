<?php
include 'db.php';

$title = $_POST['title'];
$description = $_POST['description'];

// Handle video upload first to get the ID
$videoPath = 'uploads/' . basename($_FILES['video']['name']);
move_uploaded_file($_FILES['video']['tmp_name'], $videoPath);

// Insert video details into the database first to get the ID
$sql = "INSERT INTO videos (title, description, thumbnail, video_path) VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$title, $description, null, $videoPath]);

// Get the last inserted ID
$lastId = $pdo->lastInsertId();

// Handle thumbnail upload and conversion to WebP
$thumbnailTempPath = $_FILES['thumbnail']['tmp_name'];
$thumbnailImage = imagecreatefromstring(file_get_contents($thumbnailTempPath));

// Check if the image was created successfully
if ($thumbnailImage !== false) {
    // Set the path for the compressed WebP image using the last ID
    $webpThumbnailPath = 'uploads/' . $lastId . '.webp';

    // Resize the image to a smaller size (optional, adjust dimensions as needed)
    $width = 200; // New width
    $height = 200; // New height
    $resizedImage = imagescale($thumbnailImage, $width, $height);

    // Save the resized image as a WebP file
    imagewebp($resizedImage, $webpThumbnailPath, 80); // 80 is the quality (0-100)

    // Free up memory
    imagedestroy($thumbnailImage);
    imagedestroy($resizedImage);

    // Update the video record with the thumbnail path
    $sql = "UPDATE videos SET thumbnail = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$webpThumbnailPath, $lastId]);
} else {
    // Handle error - image creation failed
    http_response_code(400);
    die("Thumbnail upload failed.");
}

// Set the correct video path with ID
$finalVideoPath = 'uploads/' . $lastId . '.mp4';
rename($videoPath, $finalVideoPath);

// Update the video path in the database
$sql = "UPDATE videos SET video_path = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$finalVideoPath, $lastId]);

http_response_code(200);
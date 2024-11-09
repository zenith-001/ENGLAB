<?php
include 'db.php';

$title = $_POST['title'];
$description = $_POST['description'];

$thumbnailPath = 'uploads/' . basename($_FILES['thumbnail']['name']);
move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath);

$videoPath = 'uploads/' . basename($_FILES['video']['name']);
move_uploaded_file($_FILES['video']['tmp_name'], $videoPath);

$sql = "INSERT INTO videos (title, description, thumbnail, video_path) VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$title, $description, $thumbnailPath, $videoPath]);

http_response_code(200);

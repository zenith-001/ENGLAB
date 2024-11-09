<?php
include 'db.php';

$id = $_GET['id'];
$sql = "SELECT * FROM videos WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$video = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($video['title']); ?></title>
</head>
<body>
<nav class="navbar">
        <!-- <a href="index.php"><i class="fas fa-upload"></i> Upload</a> -->
        <!-- <a href="admin.php?admin=true"><i class="fas fa-user-shield"></i> Admin</a> -->
        <a href="index.php"><i class="fas fa-list"></i> Video List</a>
    </nav>
    <h2><?php echo htmlspecialchars($video['title']); ?></h2>
    <video controls width="600">
        <source src="<?php echo $video['video_path']; ?>" type="video/mp4">
    </video>
    <p><?php echo htmlspecialchars($video['description']); ?></p>
</body>
</html>

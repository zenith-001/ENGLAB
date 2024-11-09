<?php
include 'db.php';

$sql = "SELECT * FROM videos";
$stmt = $pdo->query($sql);
$videos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Video List</title>
</head>
<body>
    <h2>Uploaded Videos</h2>
    <ul>
        <?php foreach ($videos as $video): ?>
            <li>
                <img src="<?php echo $video['thumbnail']; ?>" alt="Thumbnail" width="100">
                <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                <p><?php echo htmlspecialchars($video['description']); ?></p>
                <a href="watch.php?id=<?php echo $video['id']; ?>">Watch</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>

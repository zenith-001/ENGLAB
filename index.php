<?php
include 'db.php';

$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Prepare the search parameters
$searchTitle = "%$search%";
$searchDescription = "%$search%";

// Prepare the SQL statement
$stmt = $connection->prepare("SELECT * FROM videos WHERE title LIKE ? OR description LIKE ? ORDER BY upload_date DESC");
$stmt->bind_param("ss", $searchTitle, $searchDescription);
$stmt->execute();
$result = $stmt->get_result();
$videos = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Video List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <!-- Search Bar -->
    <div class="search-bar">
        <form method="get" action="index.php">
            <input type="text" name="search" placeholder="Search videos..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <?php if (count($videos) > 0): ?>
        <?php foreach ($videos as $video): ?>
            <div class="video-list-item" onclick="location.href='watch.php?id=<?php echo $video['id']; ?>'">
                <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" alt="Thumbnail">
                <div class="video-details">
                    <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                    <p><?php echo htmlspecialchars($video['description']); ?></p>
                    <a href="watch.php?id=<?php echo $video['id']; ?>"><i class="fas fa-play"></i> Watch</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No videos found.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$connection->close();
?>
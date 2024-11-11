
<?php include 'db.php'; ?>

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
            <input type="text" name="search" placeholder="Search videos..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <?php
    // Get the search keyword if it exists
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Modify the SQL query to include the search condition
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE title LIKE :search OR description LIKE :search ORDER BY upload_date DESC");
    $stmt->execute(['search' => "%$search%"]);
    $videos = $stmt->fetchAll();

    // Check if any videos match the search
    if (count($videos) > 0):
        foreach ($videos as $video):
    ?>
        <div onclick="location.href='watch.php?id=<?php echo $video['id']; ?>'"  class="video-list-item">
            <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" alt="Thumbnail">
            <div class="video-details">
                <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                <p><?php echo htmlspecialchars($video['description']); ?></p>
                <a href="watch.php?id=<?php echo $video['id']; ?>"><i class="fas fa-play"></i> Watch</a>
            </div>
        </div>
    <?php
        endforeach;
    else:
        echo "<p>No videos found.</p>";
    endif;
    ?>
</div>

</body>
</html>

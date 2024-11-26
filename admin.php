<?php
include 'db.php';

if (!isset($_GET['admin']) || $_GET['admin'] !== 'true') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];

        // Fetch the video_path and thumbnail before deleting
        $sql = "SELECT video_path, thumbnail FROM videos WHERE id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $video = $stmt->get_result()->fetch_assoc();

        // Check if the video exists and the paths are valid
        if ($video) {
            // Delete the associated files
            if (file_exists($video['video_path'])) {
                unlink($video['video_path']);  // Delete the video file
            }
            if (file_exists($video['thumbnail'])) {
                unlink($video['thumbnail']);  // Delete the thumbnail file
            }

            // Now delete the record from the database
            $sql = "DELETE FROM videos WHERE id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $sql = "UPDATE videos SET title = ?, description = ? WHERE id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $id);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
}

$sql = "SELECT * FROM videos";
$stmt = $connection->query($sql);
$videos = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <style>
        li {
            margin: 20px;
        }
    </style>
    <title>Admin Panel</title>
</head>
<body>
    <nav class="navbar">
        <a href="list.php"><i class="fas fa-upload"></i> Upload</a>
        <a href="index.php"><i class="fas fa-list"></i> Video List</a>
        <a href="admin.php?admin=true"><i class="fas fa-user-shield"></i> Admin</a>
    </nav>
    <h2>Admin Control Panel</h2>
    <ol>
        <?php foreach ($videos as $video): ?>
            <li>
                <form method="post">
                    <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" alt="Thumbnail" height="200">
                    <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($video['title']); ?>">
                    <textarea name="description"><?php echo htmlspecialchars($video['description']); ?></textarea>
                    <button type="submit" name="edit">Edit</button>
                    <button type="submit" name="delete">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ol>
</body>
</html>
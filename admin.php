<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM videos WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $sql = "UPDATE videos SET title = ?, description = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $id]);
    }
}

$sql = "SELECT * FROM videos";
$stmt = $pdo->query($sql);
$videos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
</head>
<body>
    <h2>Admin Control Panel</h2>
    <ul>
        <?php foreach ($videos as $video): ?>
            <li>
                <img src="<?php echo $video['thumbnail']; ?>" alt="Thumbnail" width="100">
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($video['title']); ?>">
                    <textarea name="description"><?php echo htmlspecialchars($video['description']); ?></textarea>
                    <button type="submit" name="edit">Edit</button>
                    <button type="submit" name="delete">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>

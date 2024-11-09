<?php
include 'db.php';

if (!isset($_GET['admin']) || $_GET['admin'] !== 'true') {
    header("Location: list.php");
    exit();
}
?>
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
    <link rel="stylesheet" href="style.css">
    <style>
        li{
            margin: 20px;
        }
    </style>
    <title>Admin Panel</title>
</head>
<body>
    <nav class="navbar">
        <a href="index.php"><i class="fas fa-upload"></i> Upload</a>
        <a href="list.php"><i class="fas fa-list"></i> Video List</a>
        <a href="admin.php?admin=true"><i class="fas fa-user-shield"></i> Admin</a>
    </nav>
    <h2>Admin Control Panel</h2>
    <ol>
        <?php foreach ($videos as $video): ?>
            <li>
                <form method="post">
                    <img src="<?php echo $video['thumbnail']; ?>" alt="Thumbnail" height="200">
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

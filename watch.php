<?php
// Include the database connection file
include 'db.php';

// Check if the video ID is set in the GET request
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the SQL statement
    $stmt = $connection->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->bind_param("i", $id); // Bind the ID parameter as an integer

    // Execute the statement
    $stmt->execute();
    
    // Fetch the results
    $result = $stmt->get_result();
    $video = $result->fetch_assoc();

    // Check if the video was found
    if ($video) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title><?php echo htmlspecialchars($video['title']); ?></title>
            <link rel="stylesheet" href="style.css">
            <style>
                /* Custom video container styles */
                .video-container {
                    background-color: #1c1c1c;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
                    max-width: 800px;
                    margin: auto;
                }

                /* Fixed size for video player */
                #videoPlayer {
                    width: 640px;  /* Set fixed width */
                    height: 360px; /* Set fixed height */
                    border-radius: 8px;
                    outline: none;
                    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.5);
                }
            </style>
        </head>
        <body>
            <nav class="navbar">
                <a href="index.php"><i class="fas fa-list"></i> Video List</a>
            </nav>
            <div class="video-container">
                <h2><?php echo htmlspecialchars($video['title']); ?></h2>
                
                <!-- Video Player with fixed size -->
                <video id="videoPlayer" controls preload="metadata">
                    <source src="<?php echo htmlspecialchars($video['video_path']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                
                <p><?php echo htmlspecialchars($video['description']); ?></p>
            </div>

            <script>
                // Show a loading indicator while the video is buffering
                var videoPlayer = document.getElementById("videoPlayer");
                videoPlayer.addEventListener("waiting", function() {
                    // Show loading indicator (you can customize this part)
                    console.log("Buffering...");
                });

                videoPlayer.addEventListener("playing", function() {
                    // Hide loading indicator
                    console.log("Playing...");
                });
            </script>
        </body>
        </html>
        <?php
    } else {
        echo "Video not found.";
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Invalid video ID.";
}

// Close the database connection
$connection->close();
?>
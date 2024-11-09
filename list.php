<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Video</title>
    <script>
        function uploadFile() {
            var fileInput = document.getElementById("video");
            var thumbnailInput = document.getElementById("thumbnail");
            var formData = new FormData();
            formData.append("video", fileInput.files[0]);
            formData.append("thumbnail", thumbnailInput.files[0]);
            formData.append("title", document.getElementById("title").value);
            formData.append("description", document.getElementById("description").value);

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "upload.php", true);

            xhr.upload.addEventListener("progress", function(event) {
                if (event.lengthComputable) {
                    var percent = (event.loaded / event.total) * 100;
                    document.getElementById("progress").value = percent;
                    document.getElementById("progress_text").innerText = Math.round(percent) + "%";
                }
            });

            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert("Video uploaded successfully");
                    window.location.href = 'index.php';
                } else {
                    alert("Upload failed");
                }
            };
            xhr.send(formData);
        }
    </script>
    <link rel="stylesheet" href="style.css">
    </head>
<body>
<nav class="navbar">
        <a href="index.php"><i class="fas fa-upload"></i> Upload</a>
        <a href="index.php"><i class="fas fa-list"></i> Video List</a>
        <a href="admin.php?admin=true"><i class="fas fa-user-shield"></i> Admin</a>
    </nav>
    <h2>Upload Video</h2>
    <form onsubmit="event.preventDefault(); uploadFile();">
        <label>Title: <input type="text" id="title" required></label><br>
        <label>Description: <textarea id="description" required></textarea></label><br>
        <label>Thumbnail: <input type="file" id="thumbnail" accept="image/*" required></label><br>
        <label>Video File: <input type="file" id="video" accept="video/mp4" required></label><br>
        <progress id="progress" value="0" max="100"></progress>
        <span id="progress_text">0%</span><br>
        <button type="submit">Upload</button>
    </form>
</body>
</html>

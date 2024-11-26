<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Upload Video</title>
    <link rel="stylesheet" href="style.css">
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

            xhr.onload = function () {
                if (xhr.status === 200) {
                    alert("Video uploaded successfully");
                    window.location.href = 'index.php';
                } else {
                    alert("Upload failed");
                }
            };

            xhr.send(formData);
            checkUploadProgress(); // Start checking upload progress
        }
        function checkUploadProgress() {
            const progressBar = document.getElementById('progress-bar');

            const interval = setInterval(() => {
                fetch('progress.php')
                    .then(response => response.json())
                    .then(data => {
                        progressBar.style.width = data.progress + '%';
                        progressBar.innerText = Math.round(data.progress) + '%';

                        if (data.progress >= 100) {
                            clearInterval(interval); // Stop checking when upload is complete
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching progress:", error);
                        clearInterval(interval); // Stop checking on error
                    });
            }, 1000); // Check progress every second
        }
    </script>
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
        <div id="progress-bar"
            style="width: 0; height: 20px; background-color: green; text-align: center; color: white;">0%</div><br>
        <button type="submit">Upload</button>
    </form>
</body>

</html>
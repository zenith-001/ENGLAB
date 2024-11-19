<?php
$host = 'localhost';
$dbname = 'video_db';
$username = 'root'; // Update if needed
$password = ''; // Update if needed

// Create connection
$connection = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Set the connection to return results as associative arrays
$connection->set_charset("utf8");
?>
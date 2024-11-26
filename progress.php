<?php
session_start(); // Make sure this is at the top of the file
header('Content-Type: application/json');

$progress = isset($_SESSION['upload_progress']) ? $_SESSION['upload_progress'] : 0;
echo json_encode(['progress' => $progress]);
?>
<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];

    // Update task status in the database
    $sql = "UPDATE tasks SET status = '$status' WHERE id = '$task_id' AND user_id = '".$_SESSION['user_id']."'";
    
    if ($conn->query($sql) === TRUE) {
        header('Location: dashboard.php');
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
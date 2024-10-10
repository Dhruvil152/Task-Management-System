<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic server-side validation
    if (empty($_POST['task_name']) || empty($_POST['deadline'])) {
        echo "Task Name and Deadline are required!";
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $task_name = $conn->real_escape_string($_POST['task_name']);
    $deadline = $conn->real_escape_string($_POST['deadline']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $category = $conn->real_escape_string($_POST['category']);
    $dependency_task_id = !empty($_POST['dependency_task_id']) ? $_POST['dependency_task_id'] : 'NULL';

    // Handle custom label creation
    $label_id = !empty($_POST['label_id']) ? $_POST['label_id'] : 'NULL';
    if (!empty($_POST['new_label'])) {
        $new_label = $conn->real_escape_string($_POST['new_label']);
        $sql = "INSERT INTO labels (user_id, label_name) VALUES ('$user_id', '$new_label')";
        if ($conn->query($sql) === TRUE) {
            $label_id = $conn->insert_id;
        }
    }

    // Insert task with label into the database
    $sql = "INSERT INTO tasks (user_id, task_name, deadline, priority, category, dependency_task_id, label_id) 
            VALUES ('$user_id', '$task_name', '$deadline', '$priority', '$category', $dependency_task_id, $label_id)";
    
    if ($conn->query($sql) === TRUE) {
        header('Location: dashboard.php');
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
<?php
session_start();
require 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=tasks.csv');

$output = fopen('php://output', 'w');
fputcsv($output, array('Task Name', 'Deadline', 'Priority', 'Category', 'Status'));

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM tasks WHERE user_id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, array($row['task_name'], $row['deadline'], $row['priority'], $row['category'], $row['status']));
    }
}

fclose($output);
$conn->close();

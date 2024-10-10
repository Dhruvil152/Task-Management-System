<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Task Scheduler</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">

        <a href="logout.php" class="btn-logout">Logout</a>

        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>

        <h3>Upcoming Deadlines</h3>
        
        <h3>Your Tasks</h3>
            <div class="task-list">
                <?php
                require 'db.php';
                $user_id = $_SESSION['user_id'];  // Fetch tasks for the logged-in user
                $sql = "SELECT * FROM tasks WHERE user_id = '$user_id' ORDER BY deadline ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($task = $result->fetch_assoc()) {
                        echo '<div class="task-card">';
                        echo '<h4>' . $task['task_name'] . '</h4>';
                        echo '<p>Deadline: ' . $task['deadline'] . '</p>';
                        echo '<p>Priority: ' . $task['priority'] . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No tasks found. Add your first task!</p>';
                }
                ?>
            </div>
            <?php
            $today = date('Y-m-d');
            $upcoming_sql = "SELECT * FROM tasks WHERE user_id = '$user_id' AND deadline <= '$today' AND status != 'Completed'";
            $upcoming_tasks = $conn->query($upcoming_sql);

            if ($upcoming_tasks->num_rows > 0) {
                while ($task = $upcoming_tasks->fetch_assoc()) {
                    echo '<div class="notification">';
                    echo 'Task "' . $task['task_name'] . '" is due on ' . $task['deadline'] . '!';
                    echo '</div>';
                }
            } else {
                echo '<p>No upcoming tasks due today or overdue.</p>';
            }
            ?>

        

        <!-- Filter Tasks by Category -->
        <form action="dashboard.php" method="GET">
            <label for="category_filter">Filter by Category: </label>
            <select name="category_filter" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="Work" <?php if ($_GET['category_filter'] == 'Work') echo 'selected'; ?>>Work</option>
                <option value="Personal" <?php if ($_GET['category_filter'] == 'Personal') echo 'selected'; ?>>Personal</option>
                <option value="Urgent" <?php if ($_GET['category_filter'] == 'Urgent') echo 'selected'; ?>>Urgent</option>
                <option value="Other" <?php if ($_GET['category_filter'] == 'Other') echo 'selected'; ?>>Other</option>
            </select>
        </form>

        <!-- Task Scheduling Section -->
        <form action="add_task.php" method="POST" class="task-form" name="taskForm" onsubmit="return validateTaskForm()">
            <input type="text" name="task_name" placeholder="Task Name" required>
            <input type="date" name="deadline" placeholder="Deadline" required>
            <select name="priority" required>
                <option value="" disabled selected>Select Priority</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
            
            <!-- Task Category Dropdown -->
            <select name="category" required>
                <option value="" disabled selected>Select Category</option>
                <option value="Work">Work</option>
                <option value="Personal">Personal</option>
                <option value="Urgent">Urgent</option>
                <option value="Other">Other</option>
            </select>

            <!-- Task Dependency Dropdown -->
            <select name="dependency_task_id">
                <option value="">No Dependency</option>
                <?php
                require 'db.php';
                $user_id = $_SESSION['user_id'];
                $sql = "SELECT * FROM tasks WHERE user_id = '$user_id'";
                $dependencyTasks = $conn->query($sql);
                while ($task = $dependencyTasks->fetch_assoc()) {
                    echo '<option value="' . $task['id'] . '">' . $task['task_name'] . '</option>';
                }
                ?>
            </select>

            <!-- Select an existing label or add a new one -->
            <select name="label_id">
                <option value="">No Label</option>
                <?php
                // Fetch existing labels for the current user
                $sql = "SELECT * FROM labels WHERE user_id = '$user_id'";
                $labels = $conn->query($sql);
                while ($label = $labels->fetch_assoc()) {
                    echo '<option value="' . $label['id'] . '">' . $label['label_name'] . '</option>';
                }
                ?>
            </select>
            <!-- Option to add a new label -->
            <input type="text" name="new_label" placeholder="Create new label (optional)">

            <button type="submit">Add Task</button>
                        
        </form>

        <!-- Daily Planner Section -->
        <h3>Today's Tasks</h3>

        <form action="dashboard.php" method="GET">
            <input type="text" name="search" placeholder="Search tasks..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
            <button type="submit">Search</button>
        </form>

        <?php
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $category_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : '';
            
            $sql = "SELECT * FROM tasks WHERE user_id = '$user_id'";
            
            // Apply search filter if set
            if (!empty($search)) {
                $sql .= " AND task_name LIKE '%$search%'";
            }
            
            // Apply category filter if set
            if (!empty($category_filter)) {
                $sql .= " AND category = '$category_filter'";
            }
            
            $sql .= " ORDER BY deadline ASC";
            
            $allTasks = $conn->query($sql);
        ?>

        <form action="dashboard.php" method="GET">
            <label for="sort_by">Sort by: </label>
            <select name="sort_by" onchange="this.form.submit()">
                <option value="deadline" <?php if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'deadline') echo 'selected'; ?>>Deadline</option>
                <option value="priority" <?php if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'priority') echo 'selected'; ?>>Priority</option>
            </select>
        </form>

        <?php
            $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'deadline';
            $order_by = $sort_by == 'priority' ? "FIELD(priority, 'High', 'Medium', 'Low')" : 'deadline';
            
            $sql = "SELECT * FROM tasks WHERE user_id = '$user_id'";
            
            // Apply search filter if set
            if (!empty($search)) {
                $sql .= " AND task_name LIKE '%$search%'";
            }
            
            // Apply category filter if set
            if (!empty($category_filter)) {
                $sql .= " AND category = '$category_filter'";
            }
            
            // Sort by deadline or priority
            $sql .= " ORDER BY $order_by ASC";
            
            $allTasks = $conn->query($sql);
        ?>


        

        <!-- Progress Tracking Section -->
        <h3>Progress Tracking</h3>
        <?php
        // Fetch all tasks
        $sql = "SELECT * FROM tasks WHERE user_id = '$user_id'";
        $allTasks = $conn->query($sql);
        $totalTasks = $allTasks->num_rows;

        // Fetch completed tasks
        $sql = "SELECT * FROM tasks WHERE user_id = '$user_id' AND status = 'Completed'";
        $completedTasks = $conn->query($sql);
        $completedCount = $completedTasks->num_rows;

        if ($totalTasks > 0) {
            $progressPercentage = ($completedCount / $totalTasks) * 100;
        } else {
            $progressPercentage = 0;
        }
        ?>

        <div class="progress-bar">
            <div class="progress" style="width: <?php echo $progressPercentage; ?>%;"></div>
        </div>
        <p><?php echo round($progressPercentage); ?>% of tasks completed.</p>

        <!-- All Tasks Section -->
        <h3>All Tasks</h3>
        <div class="task-list">
            <?php
            $category_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : '';

            $sql = "SELECT * FROM tasks WHERE user_id = '$user_id'";
            if (!empty($category_filter)) {
                $sql .= " AND category = '$category_filter'";
            }
            $sql .= " ORDER BY deadline ASC";

            $allTasks = $conn->query($sql);
            if ($allTasks->num_rows > 0) {
                while ($task = $allTasks->fetch_assoc()) {
                    // Assign class based on priority
                    $priorityClass = strtolower($task['priority']) . '-priority';

                    echo '<div class="task-card ' . $priorityClass . '">';
                    echo '<h4>' . $task['task_name'] . '</h4>';
                    echo '<p>Deadline: ' . $task['deadline'] . '</p>';
                    echo '<p>Priority: ' . $task['priority'] . '</p>';

                    // Display task label
                    if (!empty($task['label_id'])) {
                        $sql_label = "SELECT * FROM labels WHERE id = '" . $task['label_id'] . "'";
                        $label_result = $conn->query($sql_label);
                        if ($label_result->num_rows > 0) {
                            $label = $label_result->fetch_assoc();
                            echo '<p>Label: ' . $label['label_name'] . '</p>';
                        }
                    }

                    // Task Status Update Form
                    echo '<form action="update_task_status.php" method="POST">';
                    echo '<input type="hidden" name="task_id" value="' . $task['id'] . '">';
                    echo '<label for="status">Status: </label>';
                    echo '<select name="status" onchange="this.form.submit()">';
                    echo '<option value="Not Started"' . ($task['status'] == 'Not Started' ? ' selected' : '') . '>Not Started</option>';
                    echo '<option value="In Progress"' . ($task['status'] == 'In Progress' ? ' selected' : '') . '>In Progress</option>';
                    echo '<option value="Completed"' . ($task['status'] == 'Completed' ? ' selected' : '') . '>Completed</option>';
                    echo '</select>';
                    echo '</form>';

                    // Task Dependency Logic
                    if ($task['dependency_task_id'] != NULL) {
                        $dependency_id = $task['dependency_task_id'];
                        $sql_dependency = "SELECT * FROM tasks WHERE id = '$dependency_id'";
                        $dependency_result = $conn->query($sql_dependency);
                        if ($dependency_result->num_rows > 0) {
                            $dependency_task = $dependency_result->fetch_assoc();
                            if ($dependency_task['status'] != 'Completed') {
                                echo '<p>Cannot complete until "' . $dependency_task['task_name'] . '" is finished.</p>';
                            }
                        }
                    }

                    echo '</div>';
                }
            } else {
                echo '<p>No tasks found.</p>';
            }
            ?>
        </div>

        <!-- Export Buttons -->
        <a href="export_csv.php" class="btn-export">Export Tasks as CSV</a>
        <a href="export_pdf.php" class="btn-export">Export Tasks as PDF</a>

    </div>

    <script>
        function validateTaskForm() {
            var taskName = document.forms["taskForm"]["task_name"].value;
            var deadline = document.forms["taskForm"]["deadline"].value;
            if (taskName == "" || deadline == "") {
                alert("Task Name and Deadline must be filled out");
                return false;
            }
            return true;
        }
    </script>

    <!-- JavaScript for smooth page transition -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.body.classList.remove("loading");
        });

        window.addEventListener("beforeunload", function() {
            document.body.classList.add("loading");
        });
    </script>

</body>
</html>

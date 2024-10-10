# Task Management System

This is a web-based Task Management System developed using **HTML**, **CSS**, **JavaScript (JS)**, **PHP**, and **MySQL**. The system allows users to manage their tasks efficiently by adding, updating, and tracking their progress. It also includes features for exporting task data as a PDF.

## Features

1. **User Authentication**:
   - **Login/Logout**: Users can log in to access the task management system using `login.php` and log out via `logout.php`.
   - **Register**: New users can register and create an account using `register.php`.

2. **Task Management**:
   - **Add New Task**: Users can add tasks via `add_task.php`.
   - **Update Task Status**: Task status can be updated (e.g., completed, in-progress) via `update_task_status.php`.

3. **Dashboard**:
   - **Dashboard Overview**: The `dashboard.php` serves as the central place where users can see their tasks and manage them.

4. **Data Export**:
   - **Export CSV**: Users can export their task data as a CSV file using `export_csv.php`.
   - **Export PDF**: Task data can be exported as a PDF using the `fpdf` library integrated in `export_pdf.php`.

## Technologies Used

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **PDF Export**: FPDF Library

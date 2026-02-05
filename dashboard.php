<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all students
$stmt = $conn->prepare('SELECT student_id, name, email FROM Students ORDER BY name');
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css"/>
</head>
<body>
<nav class="navbar navbar-light bg-light">
    <div class="container">
        <a href="dashboard.php" class="navbar-brand">Dashboard</a>
        <a href="define_rubric.php" class="nav-link">Define Rubric</a>
        <a href="students.php" class="nav-link">Manage Students</a>
        <a href="bulk_add_students.php" class="nav-link">Edit Coursework</a>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
    </div>
</nav>
<div class="container mt-4">
    <div class="mb-3">
        <a href="add_coursework.php" class="btn btn-primary me-2">Edit Coursework</a>
        <a href="define_rubric.php" class="btn btn-warning me-2">Define Rubric</a>
        <a href="students.php" class="btn btn-info">Manage Students</a>
    </div>
    <h2>Dashboard</h2>
    <p>Welcome to the dashboard. Below is the list of students:</p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td><?= htmlspecialchars($student['email']) ?></td>
                <td>
                    <a href="student_report.php?student_id=<?= $student['student_id'] ?>" class="btn btn-primary btn-sm">View Report</a>
                    <a href="edit_report_card.php?student_id=<?= $student['student_id'] ?>" class="btn btn-warning btn-sm">Edit Report</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

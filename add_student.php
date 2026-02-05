<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['teacher_id'])) { header('Location: login.php'); exit(); }
$msg = '';
if ($_SERVER['REQUEST_METHOD']=='POST'){
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  if ($name && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $stmt = $conn->prepare('SELECT student_id FROM Students WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
      $msg = '<div class="alert alert-warning">Email already exists!</div>';
    } else {
      $stmt = $conn->prepare('INSERT INTO Students (name, email) VALUES (?, ?)');
      $stmt->bind_param('ss', $name, $email);
      if ($stmt->execute()) {
        // Automatically create a submission for the new student
        $student_id = $stmt->insert_id;
        $stmt2 = $conn->prepare('INSERT INTO Submissions (student_id, status, created_at, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $status = 'not_submitted';
        $stmt2->bind_param('is', $student_id, $status);
        $stmt2->execute();
        $stmt2->close();

        $msg = '<div class="alert alert-success">Student added and submission created!</div>';
      } else {
        $msg = '<div class="alert alert-danger">Error: '.htmlspecialchars($conn->error).'</div>';
      }
    }
    $stmt->close();
  } else {
    $msg = '<div class="alert alert-warning">Valid name and email are required.</div>';
  }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Add Student</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css"/></head><body>
<nav class="navbar navbar-light bg-light">
    <div class="container">
        <a href="dashboard.php" class="navbar-brand">Dashboard</a>
        <a href="define_rubric.php" class="nav-link">Define Rubric</a>
        <a href="students.php" class="nav-link">Manage Students</a>
        <a href="bulk_add_students.php" class="nav-link">Edit Coursework</a>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
    </div>
</nav>
<div class="container mt-5" style="max-width:500px;">
    <h2>Add Student</h2>
    <?= $msg ?>
    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Student</button>
    </form>
</div>
</body></html>

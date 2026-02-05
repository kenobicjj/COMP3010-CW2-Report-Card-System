<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['teacher_id'])) { header('Location: login.php'); exit(); }
$msg = '';
$stmt = $conn->prepare('SELECT * FROM Students ORDER BY name');
$stmt->execute();
$res = $stmt->get_result();
$students = [];
while($row=$res->fetch_assoc()) $students[] = $row;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>All Students</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css"/>
</head><body>
<nav class="navbar navbar-light bg-light">
    <div class="container">
        <a href="dashboard.php" class="navbar-brand">Dashboard</a>
        <a href="define_rubric.php" class="nav-link">Define Rubric</a>
        <a href="students.php" class="nav-link">Manage Students</a>
        <a href="bulk_add_students.php" class="nav-link">Edit Coursework</a>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
    </div>
</nav>
<div class="container mt-5" style="max-width:900px;">
<h2>Student List</h2>
<div class="mb-3">
  <a href="bulk_add_students.php" class="btn btn-info me-2">Bulk Add Students</a>
  <a href="add_student.php" class="btn btn-success">Add Single Student</a>
</div>
<table class="table table-bordered table-striped"><thead>
  <tr><th>Name</th><th>Email</th></tr>
</thead><tbody>
<?php foreach ($students as $s): ?>
  <tr><td><?=htmlspecialchars($s['name'])?></td><td><?=htmlspecialchars($s['email'])?></td></tr>
<?php endforeach; ?>
</tbody></table>
</div></body></html>

<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['teacher_id'])) { header('Location: login.php'); exit(); }
$msg = '';
$summary = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csv = trim($_POST['students_csv'] ?? '');
    if ($csv) {
        $lines = preg_split("/[\r\n]+/", $csv);
        foreach($lines as $line) {
            if (!$line) continue;
            $parts = str_getcsv($line);
            $name = trim($parts[0] ?? '');
            $email = trim($parts[1] ?? '');
            if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $summary[] = "Skipped: [$line] - invalid or missing fields.";
                continue;
            }
            // Insert student if not exists
            $stmt = $conn->prepare('SELECT student_id FROM Students WHERE email=?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $summary[] = "Duplicate: $email (already exists).";
            } else {
                $stmt2 = $conn->prepare('INSERT INTO Students (name, email) VALUES (?, ?)');
                $stmt2->bind_param('ss', $name, $email);
                $stmt2->execute();
                $stmt2->close();
                $summary[] = "Added: $name ($email)";
            }
            $stmt->close();
        }
        $msg='<div class="alert alert-info">Import complete.</div>';
    } else {
        $msg='<div class="alert alert-warning">Paste CSV records first.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Bulk Add Students</title>
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
<h2>Bulk Add Students</h2>
<?=$msg?>
<form method="post">
  <div class="mb-2"><label>CSV Data (Name, Email)</label>
    <textarea name="students_csv" class="form-control" rows="10" required></textarea>
  </div>
  <button class="btn btn-success mt-2">Add Students</button>
  <a href="students.php" class="btn btn-secondary mt-2">Back</a>
</form>
<?php if ($summary): ?><h4 class="mt-4">Import Summary</h4><ul class="list-group"><?php foreach($summary as $item): ?><li class="list-group-item small"> <?=$item?> </li><?php endforeach; ?></ul><?php endif; ?>
</div></body></html>

<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['teacher_id'])) { header('Location: login.php'); exit(); }
$msg = '';

// Handle deletion of a rubric component
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare('DELETE FROM ComponentGrades WHERE component_id = ?');
    $stmt->bind_param('i', $del_id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare('DELETE FROM RubricComponents WHERE component_id = ?');
    $stmt->bind_param('i', $del_id);
    $stmt->execute();
    $stmt->close();
    $msg = '<div class="alert alert-success">Rubric component deleted and all relevant grades removed.</div>';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['component_name']);
    $wgt = floatval($_POST['weight']);
    $maxm = floatval($_POST['max_marks']);
    $desc = trim($_POST['description']);
    $order = intval($_POST['display_order']);

    if ($name && $wgt && $maxm && $order) {
        $stmt = $conn->prepare('INSERT INTO RubricComponents (component_name, weight, max_marks, display_order) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sddi', $name, $wgt, $maxm, $order);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">Rubric component added successfully.</div>';
        } else {
            $msg = '<div class="alert alert-danger">Error: '.htmlspecialchars($conn->error).'</div>';
        }
        $stmt->close();
    } else {
        $msg = '<div class="alert alert-warning">All fields are required.</div>';
    }
}

$stmt = $conn->prepare('SELECT * FROM RubricComponents ORDER BY display_order');
$stmt->execute();
$res = $stmt->get_result();
$components = [];
while ($row = $res->fetch_assoc()) $components[] = $row;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Define Rubric</title>
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
    <h2>Define Rubric</h2>
    <?= $msg ?>
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="component_name" class="form-label">Component Name</label>
            <input type="text" class="form-control" id="component_name" name="component_name" required>
        </div>
        <div class="mb-3">
            <label for="weight" class="form-label">Weight (%)</label>
            <input type="number" step="0.01" class="form-control" id="weight" name="weight" required>
        </div>
        <div class="mb-3">
            <label for="max_marks" class="form-label">Max Marks</label>
            <input type="number" step="0.01" class="form-control" id="max_marks" name="max_marks" required>
        </div>
        <div class="mb-3">
            <label for="display_order" class="form-label">Display Order</label>
            <input type="number" class="form-control" id="display_order" name="display_order" required>
        </div>
        <button type="submit" name="add" class="btn btn-primary">Add Component</button>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Component Name</th>
                <th>Weight</th>
                <th>Max Marks</th>
                <th>Display Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($components as $c): ?>
            <tr>
                <td><?=htmlspecialchars($c['component_name'])?></td>
                <td><?=htmlspecialchars($c['weight'])?></td>
                <td><?=htmlspecialchars($c['max_marks'])?></td>
                <td><?=htmlspecialchars($c['display_order'])?></td>
                <td>
                    <a href="define_rubric.php?delete=<?=$c['component_id']?>" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

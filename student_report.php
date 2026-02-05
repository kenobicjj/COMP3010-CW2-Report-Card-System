<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = intval($_GET['student_id'] ?? 0);
if (!$student_id) {
    echo 'Missing student ID.';
    exit();
}

// Fetch student details
$stmt = $conn->prepare("SELECT * FROM Students WHERE student_id = ?");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$stu = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$stu) {
    echo "Student not found.";
    exit();
}

// Fetch submissions and grades
$stmt = $conn->prepare(
    "SELECT rc.component_name, rc.max_marks, rc.weight, cg.marks_obtained, cg.feedback
    FROM RubricComponents rc
    LEFT JOIN ComponentGrades cg ON rc.component_id = cg.component_id
    WHERE cg.submission_id = (SELECT submission_id FROM Submissions WHERE student_id = ?)"
);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate total weighted marks
$total_marks = 0;
$total_weighted_marks = 0;
$total_weight = 0;
foreach ($report_data as $row) {
    $marks_obtained = $row['marks_obtained'] ?? 0;
    $weight = $row['weight'] ?? 0;
    $total_marks += ($marks_obtained * $weight / 100);
    $weighted_marks = ($marks_obtained / $row['max_marks']) * $weight;
    $total_weighted_marks += $weighted_marks;
    $total_weight += $weight;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card for <?=htmlspecialchars($stu['name'])?></title>
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
    <h2>Report Card: <?=htmlspecialchars($stu['name'])?></h2>
    <p>Email: <?=htmlspecialchars($stu['email'])?></p>

    <?php if (empty($report_data)): ?>
        <p>No submissions or grades available for this student.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Max Marks</th>
                    <th>Weight (%)</th>
                    <th>Marks Obtained</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_data as $row): ?>
                    <tr>
                        <td><?=htmlspecialchars($row['component_name'])?></td>
                        <td><?=htmlspecialchars($row['max_marks'])?></td>
                        <td><?=htmlspecialchars($row['weight'])?></td>
                        <td><?=htmlspecialchars($row['marks_obtained'])?></td>
                        <td><?=htmlspecialchars($row['feedback'])?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total Weighted Marks:</strong></td>
                    <td colspan="2"> <?= number_format($total_weighted_marks, 2) ?> / <?= number_format($total_weight, 2) ?> </td>
                </tr>
            </tfoot>
        </table>
        <h3 class="mt-4">Total Marks: <?=htmlspecialchars($total_marks)?></h3>
        <a href="generate_report_card.php?student_id=<?= $student_id ?>" class="btn btn-primary">Generate Report Card</a>
    <?php endif; ?>
</div>
</body>
</html>

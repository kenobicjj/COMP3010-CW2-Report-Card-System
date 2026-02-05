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

// Handle form submission to update grades
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['grades'] as $component_id => $marks_obtained) {
        $feedback = $_POST['feedback'][$component_id] ?? '';
        $stmt = $conn->prepare(
            "UPDATE ComponentGrades SET marks_obtained = ?, feedback = ?, graded_date = CURRENT_TIMESTAMP 
            WHERE submission_id = (SELECT submission_id FROM Submissions WHERE student_id = ?) AND component_id = ?"
        );
        $stmt->bind_param('dsii', $marks_obtained, $feedback, $student_id, $component_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: dashboard.php');
    exit();
}

// Fetch submissions and grades
$stmt = $conn->prepare(
    "SELECT rc.component_id, rc.component_name, rc.max_marks, rc.weight, cg.marks_obtained, cg.feedback
    FROM RubricComponents rc
    LEFT JOIN ComponentGrades cg ON rc.component_id = cg.component_id
    WHERE cg.submission_id = (SELECT submission_id FROM Submissions WHERE student_id = ?)
    ORDER BY rc.component_id"
);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_weighted_marks = 0;
$total_weight = 0;
foreach ($report_data as $row) {
    $weighted_marks = ($row['marks_obtained'] / $row['max_marks']) * $row['weight'];
    $total_weighted_marks += $weighted_marks;
    $total_weight += $row['weight'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Report Card for <?=htmlspecialchars($stu['name'])?></title>
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
    <h2>Edit Report Card: <?=htmlspecialchars($stu['name'])?></h2>
    <p>Email: <?=htmlspecialchars($stu['email'])?></p>

    <?= $msg ?? '' ?>

    <?php if (empty($report_data)): ?>
        <p>No submissions or grades available for this student.</p>
    <?php else: ?>
        <form method="POST">
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
                            <td>
                                <input type="number" step="0.01" class="form-control" name="grades[<?= $row['component_id'] ?>]" value="<?=htmlspecialchars($row['marks_obtained'])?>" max="<?=htmlspecialchars($row['max_marks'])?>">
                            </td>
                            <td>
                                <textarea class="form-control" name="feedback[<?= $row['component_id'] ?>]"><?=htmlspecialchars($row['feedback'])?></textarea>
                            </td>
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
            <button type="submit" class="btn btn-primary">Update Report Card</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
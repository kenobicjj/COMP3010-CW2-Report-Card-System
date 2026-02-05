<?php
session_start();

require_once 'db.php';

$message = '';
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare('SELECT * FROM Teachers WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Note: For production use password hashing!
        // Use SHA2 hashing for password verification
        if (hash('sha256', $password) === $row['password']) {
            $_SESSION['teacher_id'] = $row['teacher_id'];
            $_SESSION['admin_name'] = $row['name'];
            header('Location: dashboard.php');
            exit();
        } else {
            $message = 'Invalid password!';
        }
    } else {
        $message = 'Invalid email!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css"/>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
<div class="card p-4" style="min-width:350px;">
    <h2 class="mb-3">Teacher Login</h2>
    <?php if ($message): ?>
        <div class="alert alert-warning"><?=$message?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>
</div>
</body>
</html>

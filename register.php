<?php
include 'config.php'; // PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json');

    $errors = [];
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    // === Validations ===
    if (!$username || strlen($username) < 3) $errors['username'] = 'Username must be at least 3 characters.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email address.';
    if (!$password || strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors['confirm'] = 'Passwords do not match.';

    // Check uniqueness
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $email]);
        if ($row = $stmt->fetch()) {
            $stmt2 = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $stmt2->execute([$username]);
            if ($stmt2->fetch()) $errors['username'] = 'Username already exists.';

            $stmt2 = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt2->execute([$email]);
            if ($stmt2->fetch()) $errors['email'] = 'Email already exists.';
        }
    }

    if ($errors) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // All good: insert user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $activation_code = bin2hex(random_bytes(16));
    $is_active = 0;
    $created_at = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, activation_code, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$username, $email, $hash, $activation_code, $is_active, $created_at]);

    echo json_encode([
        'success' => true,
        'activation_code' => $activation_code
    ]);
    exit;
}

// === If GET request, show the form ===
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Dark Mode Toggle -->
  <div class="dark-mode-toggle">
    <button id="themeToggle"><i class="bi bi-moon"></i></button>
  </div>

<div class="form-wizard">
    <h4 class="text-center mb-4 fw-bold text-primary">Create Your Account</h4>
    <form id="registerForm" method="POST">
        <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3"><label>Confirm Password</label><input type="password" name="confirm" class="form-control" required></div>
        <button type="submit" class="btn btn-success w-100 mt-2">Register</button>
    </form>

    <div id="successScreen" class="success-screen text-center" style="display:none; padding:30px;"></div>

    <p class="text-center mt-3">
        Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-underline">Sign In</a>
    </p>
</div>

<script src="script.js?v=register"></script>
</body>
</html>

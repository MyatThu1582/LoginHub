<?php
date_default_timezone_set('Asia/Yangon');
include 'config.php';

$message = '';
$error = '';

if (isset($_GET['reset_token'])) {
    $reset_token = $_GET['reset_token'];

    // Find valid reset record
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE reset_token = ? AND expires_at > NOW() LIMIT 1");
    $stmt->execute([$reset_token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        $error = "Invalid or expired reset link.";
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'errors' => [], 'message' => ''];

    if (!$reset) {
        $response['message'] = "Invalid or expired reset link.";
        echo json_encode($response);
        exit;
    }

    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm'] ?? '');

    if (strlen($password) < 6) {
        $response['errors']['password'] = "Password must be at least 6 characters long.";
    } 
    if ($password !== $confirm) {
        $response['errors']['confirm'] = "Passwords do not match.";
    }

    if (!$response['errors']) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hashed, $reset['email']]);
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$reset['email']]);

        $response['success'] = true;
        $response['message'] = "Password reset successful! You can now <a href='login.php'>login</a>.";
    }

    echo json_encode($response);
    exit;
}

} else {
    $error = "No reset token found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dark-mode-toggle">
  <button id="themeToggle"><i class="bi bi-moon"></i></button>
</div>

<div class="form-wizard">
  <h4 class="text-center mb-4 fw-bold text-primary">Reset Your Password</h4>

  <?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php else: ?>
  <form action="reset_password.php?reset_token=<?= htmlspecialchars($reset_token) ?>" method="POST">
    <div class="mb-3">
      <label>New Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Confirm Password</label>
      <input type="password" name="confirm" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success w-100">Update Password</button>
  </form>
  <?php endif; ?>

  <p class="text-center mt-3">
    Remembered your password? 
    <a href="login.php" class="text-primary fw-bold text-decoration-underline">Sign In</a>
  </p>
</div>

<script src="script.js?v=reset_password"></script>
</body>
</html>

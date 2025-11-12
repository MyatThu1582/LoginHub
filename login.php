<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']); // Remember Me
    $errors = [];

    // Validate
    if (!$email) $errors['email'] = 'Email is required';
    if (!$password) $errors['password'] = 'Password is required';

    if ($errors) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Check user
    $stmt = $pdo->prepare("SELECT id, username, email, password, is_active FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'errors' => ['email' => 'Email not found']]);
        exit;
    }

    if (!$user['is_active']) {
        echo json_encode(['success' => false, 'message' => 'Account not activated. Check your email.']);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'errors' => ['password' => 'Incorrect password']]);
        exit;
    }

    // Success: set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    // Handle Remember Me
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expire = date('Y-m-d H:i:s', time() + 30*24*60*60); // 30 days
        $pdo->prepare("UPDATE users SET remember_token = ?, remember_expire = ? WHERE id = ?")
            ->execute([password_hash($token, PASSWORD_BCRYPT), $expire, $user['id']]);
        setcookie('remember_me', $token, time() + 30*24*60*60, '/', '', true, true); // secure + httpOnly
    }

    echo json_encode(['success' => true]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Dark Mode Toggle -->
  <div class="dark-mode-toggle">
    <button id="themeToggle"><i class="bi bi-moon"></i></button>
  </div>

  <!-- Sign In Form -->
  <div class="signin-form">
    <h4 class="text-center mb-4 fw-bold text-primary">Welcome Back, Please Login!</h4>

    <!-- Success / Error Message Placeholder -->
    <div id="loginMessage" class="text-center mt-3"></div>
    <form id="loginForm">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="d-flex justify-content-between">
        <div class="mb-3 form-check">
          <input type="checkbox" name="remember" class="form-check-input" id="rememberMe">
          <label class="form-check-label" for="rememberMe">Remember Me</label>
        </div>
        <!-- Forgot password link -->
        <div class="text-end mb-3">
          <a href="forgot_password.php" id="forgotPasswordLink" class="text-primary text-decoration-underline">Forgot Password?</a>
        </div>
      </div>
      <button type="submit" class="btn btn-primary mt-2 w-100">Login</button>


      <div class="social-login text-center mb-3 mt-3">
        <div class="mt-4 mb-4">
          <hr class="border-secondary">
          <span class="position-absolute start-50 translate-middle bg-white px-2 text-muted" style="top: 73%;">Or Continue with</span>
        </div>
        <button type="button" class="btn btn-outline-primary"><i class="bi bi-facebook"></i> Facebook</button>
        <button type="button" class="btn btn-outline-info"><i class="bi bi-twitter"></i> Twitter</button>
        <button type="button" class="btn btn-outline-danger"><i class="bi bi-google"></i> Google</button>
      </div>

      <p class="text-center">
        Don't have an account? 
        <a href="register.php" class="text-primary fw-bold text-decoration-underline">Sign Up</a>
      </p>
    </form>

  </div>

  <script src="script.js?v=login"></script>
</body>
</html>

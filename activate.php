<?php
session_start(); // start session
include 'config.php'; // PDO connection

$code = $_GET['code'] ?? '';

if (!$code) {
    die('<div class="text-center mt-5"><h3>Invalid activation code ❌</h3></div>');
}

// Find user by activation code
$stmt = $pdo->prepare("SELECT id, username, is_active FROM users WHERE activation_code = ?");
$stmt->execute([$code]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('<div class="text-center mt-5"><h3>Activation code not found ❌</h3></div>');
}

// Activate user if not active
if (!$user['is_active']) {
    $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
    $stmt->execute([$user['id']]);
}

// Auto-login
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

// Dashboard URL
$dashboardURL = 'dashboard.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account Activated</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #e0f0ff, #f4f7fb);
      font-family: "Poppins", sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .activation-card {
      background: #fff;
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 450px;
      width: 100%;
      transition: all 0.3s ease;
    }
    .activation-card i {
      font-size: 60px;
      color: #28a745;
      margin-bottom: 20px;
    }
    .activation-card h2 {
      margin-bottom: 15px;
      font-weight: 600;
    }
    .activation-card p {
      margin-bottom: 25px;
      color: #555;
    }
    .activation-card a.btn {
      border-radius: 10px;
      padding: 10px 25px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .activation-card a.btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0,123,255,0.2);
    }
  </style>
</head>
<body>

<div class="activation-card">
  <i class="bi bi-check-circle-fill"></i>
  <h2>Account Activated!</h2>
  <p>Your account has been successfully activated. You are now logged in and can access your dashboard.</p>
  <a href="<?php echo $dashboardURL; ?>" class="btn btn-success w-100">Go to Dashboard</a>
</div>

</body>
</html>

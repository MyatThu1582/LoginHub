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

<script>
    // ==================== Reset PAssword AJAX ====================
const resetForm = document.querySelector('.form-wizard form');
if (resetForm) {
  // Find the <h4> inside .form-wizard
  const heading = document.querySelector('.form-wizard h4');
  const formMessage = document.createElement('div');
  heading.insertAdjacentElement('afterend', formMessage); // put it below <h4>

  resetForm.addEventListener('submit', e => {
    e.preventDefault();

    // Clear previous errors
    resetForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    resetForm.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    formMessage.innerHTML = '';

    const formData = new FormData(resetForm);
    const submitBtn = resetForm.querySelector('[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.classList.add('opacity-75'); }

    fetch(resetForm.action, { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('opacity-75'); }

        if (data.success) {
          resetForm.remove(); // remove form after success
          formMessage.innerHTML = `<div class="alert alert-success mt-3">${data.message}</div>`;
        } else {
          if (data.errors) {
            for (const field in data.errors) {
              const input = resetForm.querySelector(`[name="${field}"]`);
              if (input) {
                input.classList.add('is-invalid');
                const msg = document.createElement('div');
                msg.className = 'invalid-feedback';
                msg.innerText = data.errors[field];
                input.parentNode.appendChild(msg);
              }
            }
          }
          if (data.message) {
            formMessage.innerHTML = `<div class="alert alert-danger mt-3">${data.message}</div>`;
          }
        }
      })
      .catch(err => {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('opacity-75'); }
        console.error(err);
        formMessage.innerHTML = `<div class="alert alert-danger mt-3">Server error. Check console.</div>`;
      });
  });
}
</script>
</body>
</html>

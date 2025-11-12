<?php
// forgot_password.php
// Handles showing the form (GET) and processing reset requests (POST)
date_default_timezone_set('Asia/Yangon');
include 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } else {
        // Lookup user - do not reveal whether the email exists (generic response)
        $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Create table for password resets if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            email VARCHAR(255) NOT NULL,
            reset_token VARCHAR(128) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX (email),
            INDEX (reset_token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        if ($user) {
            // Generate reset_token and save
            $reset_token = bin2hex(random_bytes(16));
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            $created_at = date('Y-m-d H:i:s');

            $ins = $pdo->prepare('INSERT INTO password_resets (user_id, email, reset_token, expires_at, created_at) VALUES (?, ?, ?, ?, ?)');
            $ins->execute([$user['id'], $user['email'], $reset_token, $expires_at, $created_at]);

            // Build reset link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname($_SERVER['REQUEST_URI']), '/\\');
            $reset_link = $protocol . '://' . $host . $path . '/reset_password.php?reset_token=' . $reset_token;

            // Try to send email using PHPMailer
            try {
                if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
                    throw new \Exception('PHPMailer not installed. Run "composer require phpmailer/phpmailer" in the project root.');
                }

                require __DIR__ . '/vendor/autoload.php';

                // Use fully-qualified class names to avoid needing file-scope `use` statements
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                // SMTP settings from config.php
                $mail->isSMTP();
                $mail->Host = $mail_host;
                $mail->SMTPAuth = true;
                $mail->Username = $mail_user;
                $mail->Password = $mail_pass;
                // Mailtrap supports TLS; open port may vary
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $mail_port;

                $mail->setFrom($mail_from, $mail_from_name);
                $mail->addAddress($user['email'], $user['username'] ?? '');

                $mail->isHTML(true);
                $mail->Subject = 'Password reset request';
                $mail->Body = "<p>Hello " . htmlspecialchars($user['username'] ?? '') . ",</p>
                    <p>We received a request to reset your password. Click the link below to reset it (this link expires in 1 hour):</p>
                    <p><a href=\"" . htmlspecialchars($reset_link) . "\">Reset your password</a></p>
                    <p>If you didn't request this, you can safely ignore this email.</p>
                ";

                $mail->send();
                // Don't reveal whether email exists — generic success message
            } catch (\Exception $e) {
                // Log error for debugging and show a generic message
                error_log('Password reset email error: ' . $e->getMessage());
                // If PHPMailer missing, show actionable message to developer
                if (strpos($e->getMessage(), 'PHPMailer not installed') !== false) {
                    $error = $e->getMessage();
                }
            }
        }

        // Generic message to avoid email enumeration
        if (!$error) {
            $message = 'If an account with that email exists, a password reset link has been sent.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dark-mode-toggle">
  <button id="themeToggle"><i class="bi bi-moon"></i></button>
</div>

<div class="form-wizard">
    <h4 class="text-center mb-4 fw-bold text-primary">Forgot Your Password?</h4>

    <p class="text-center">Enter your email below and we’ll send you a link to reset your password.</p>

    <?php if ($message): ?>
        <div class="alert alert-success"><?=htmlspecialchars($message)?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <form action="forgot_password.php" method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Send Reset Link</button>
    </form>

    <p class="text-center mt-3">
        Remembered your password? 
        <a href="login.php" class="text-primary fw-bold text-decoration-underline">Sign In</a>
    </p>
</div>

<script>
const themeToggle = document.getElementById("themeToggle");
if(localStorage.getItem("theme")==="dark") document.body.classList.add("dark-mode");
themeToggle.addEventListener("click",()=>{document.body.classList.toggle("dark-mode");localStorage.setItem("theme",document.body.classList.contains("dark-mode")?"dark":"light");});
</script>
</body>
</html>

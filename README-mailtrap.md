Mailtrap integration notes

What I changed
- Added Mailtrap SMTP configuration variables to `config.php` (fill in your Mailtrap SMTP user and pass).
- Updated `forgot_password.php` to:
  - handle POST submissions, validate email, and create a `password_resets` table if missing
  - generate a secure token and insert it into `password_resets`
  - send a reset link email using PHPMailer via Mailtrap SMTP
  - show a generic message so the app does not reveal whether an email exists

How to install PHPMailer
1. Open a terminal in the project root (where `forgot_password.php` lives).
2. Run Composer to install PHPMailer:

   composer require phpmailer/phpmailer

   (On Windows PowerShell, run the same command.)

3. After installation, `vendor/autoload.php` will be available and the mail sending will work.

Mailtrap settings (in `config.php`)
- Update these variables with your Mailtrap SMTP credentials:
  - $mail_host
  - $mail_port
  - $mail_user
  - $mail_pass
  - $mail_from
  - $mail_from_name

Reset link and reset page
- The reset link points to `reset_password.php?token=...`.
- You should implement `reset_password.php` to accept the token, validate it (check expiry and match), and allow the user to set a new password. After a successful reset, delete the corresponding `password_resets` record.

Security notes
- Tokens expire after 1 hour.
- Responses are intentionally generic to avoid email enumeration.

Testing with Mailtrap
1. Fill Mailtrap credentials and install PHPMailer.
2. Create a test user in your `users` table (or register via the app).
3. Visit `forgot_password.php` and submit the user's email.
4. Open your Mailtrap Inbox — the email should appear there.

If you want, I can also add a `reset_password.php` implementation next.
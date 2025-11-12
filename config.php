<?php
$host = "localhost";
$db_name = "myproject_db";
$db_user = "root"; // your MySQL username
$db_pass = "";     // your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// -------------------------
// Mail configuration (Mailtrap)
// Fill these values with your Mailtrap SMTP credentials
// You can find them in your Mailtrap Inbox under "SMTP settings".
$mail_host = 'sandbox.smtp.mailtrap.io';
$mail_port = 2525; // or 587
$mail_user = '6081909ebbbef8';
$mail_pass = '0eb53bf70391e1';
$mail_from = 'loginhub@gmail.com';
$mail_from_name = 'LoginHub';
// -------------------------
?>

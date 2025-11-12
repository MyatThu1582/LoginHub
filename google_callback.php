<?php
require __DIR__ . '/vendor/autoload.php';
session_start();
include 'config.php'; // your PDO connection

$client = new Google\Client();
$client->setClientId('1070980726593-npmaforrqor59jfs7uo7cgqqgi1cpjno.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:8080/mt_php/Login_Register_System/google_callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $oauth = new Google\Service\Oauth2($client);
        $google_user = $oauth->userinfo->get();

        $email = $google_user->email;
        $name = $google_user->name;

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Create new user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_active, created_at) VALUES (?, ?, '', 1, ?)");
            $stmt->execute([$name, $email, date('Y-m-d H:i:s')]);
            $user_id = $pdo->lastInsertId();
            $username = $name;
        } else {
            $user_id = $user['id'];
            $username = $user['username'];
        }

        // Set session (auto-login)
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;

        header("Location: dashboard.php");
        exit;
    } else {
        echo "Google login error: " . htmlspecialchars($token['error']);
    }
} else {
    echo "No code returned by Google.";
}

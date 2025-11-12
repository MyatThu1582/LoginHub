<?php
require __DIR__ . '/vendor/autoload.php';

session_start();

$client = new Google\Client();
$client->setClientId('1070980726593-npmaforrqor59jfs7uo7cgqqgi1cpjno.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:8080/mt_php/Login_Register_System/google_callback.php');
$client->addScope('email');
$client->addScope('profile');

// Generate Google login URL
$login_url = $client->createAuthUrl();

header("Location: $login_url");
exit;

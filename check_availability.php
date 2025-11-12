<?php
include 'config.php';
$response = ['available' => true];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'];
    $value = trim($_POST['value']);

    if (in_array($field, ['email', 'username'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE $field = ? LIMIT 1");
        $stmt->execute([$value]);
        if ($stmt->rowCount() > 0) $response['available'] = false;
    } else {
        $response['available'] = false; // invalid field
    }
}

header('Content-Type: application/json');
echo json_encode($response);

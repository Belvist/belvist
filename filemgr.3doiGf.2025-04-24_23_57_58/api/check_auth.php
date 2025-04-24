<?php
session_start();
header('Content-Type: application/json');

$response = ['isAuthenticated' => false];

if (isset($_SESSION['user_id'])) {
    $response['isAuthenticated'] = true;
}

echo json_encode($response);
?>
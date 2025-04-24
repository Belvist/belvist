<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

try {
    session_destroy();
    $response['success'] = true;
    echo json_encode($response);
} catch (Exception $e) {
    $response['error'] = 'Ошибка выхода: ' . $e->getMessage();
    echo json_encode($response);
}
?>
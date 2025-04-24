<?php
session_start();
header('Content-Type: application/json');

require_once '../db_connect.php';

$response = ['success' => false, 'error' => ''];

try {
    if (!isset($_SESSION['user_id'])) {
        $response['error'] = 'Не авторизован';
        echo json_encode($response);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id, name, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $response['success'] = true;
        $response['user'] = $user;
    } else {
        $response['error'] = 'Пользователь не найден';
    }

    echo json_encode($response);
} catch (Exception $e) {
    $response['error'] = 'Ошибка сервера: ' . $e->getMessage();
    echo json_encode($response);
}
?>
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

    $data = json_decode(file_get_contents('php://input'), true);
    $phone = trim($data['phone'] ?? '');

    if (empty($phone)) {
        $response['error'] = 'Номер телефона обязателен';
        echo json_encode($response);
        exit;
    }

    if (!preg_match('/^\+7\d{10}$/', $phone)) {
        $response['error'] = 'Некорректный формат номера телефона';
        echo json_encode($response);
        exit;
    }

    // Проверяем, не занят ли номер другим пользователем
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? AND id != ?");
    $stmt->execute([$phone, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $response['error'] = 'Этот номер телефона уже используется другим пользователем';
        echo json_encode($response);
        exit;
    }

    // Обновляем номер телефона
    $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
    $stmt->execute([$phone, $_SESSION['user_id']]);
    $_SESSION['user_phone'] = $phone;

    $response['success'] = true;
    echo json_encode($response);
} catch (Exception $e) {
    $response['error'] = 'Ошибка сервера: ' . $e->getMessage();
    echo json_encode($response);
}
?>
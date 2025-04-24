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
    $old_password = $data['old_password'] ?? '';
    $new_password = $data['new_password'] ?? '';

    if (empty($old_password) || empty($new_password)) {
        $response['error'] = 'Все поля обязательны';
        echo json_encode($response);
        exit;
    }

    if (strlen($new_password) < 6) {
        $response['error'] = 'Новый пароль должен содержать минимум 6 символов';
        echo json_encode($response);
        exit;
    }

    // Проверяем старый пароль
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($old_password, $user['password'])) {
        $response['error'] = 'Неверный старый пароль';
        echo json_encode($response);
        exit;
    }

    // Хешируем новый пароль
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Обновляем пароль
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_new_password, $_SESSION['user_id']]);

    $response['success'] = true;
    echo json_encode($response);
} catch (Exception $e) {
    $response['error'] = 'Ошибка сервера: ' . $e->getMessage();
    echo json_encode($response);
}
?>
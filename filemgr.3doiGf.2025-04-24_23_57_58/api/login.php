<?php
session_start();
header('Content-Type: application/json');

require_once '../db_connect.php';

$response = ['success' => false, 'error' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';

    // Валидация входных данных
    if (empty($phone) || empty($password)) {
        $response['error'] = 'Все поля обязательны';
        echo json_encode($response);
        exit;
    }

    if (!preg_match('/^\+7\d{10}$/', $phone)) {
        $response['error'] = 'Некорректный формат номера телефона';
        echo json_encode($response);
        exit;
    }

    // Проверяем, существует ли пользователь
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $response['error'] = 'Пользователь с таким номером телефона не найден';
        echo json_encode($response);
        exit;
    }

    // Проверяем пароль
    if (!password_verify($password, $user['password'])) {
        $response['error'] = 'Неверный пароль';
        echo json_encode($response);
        exit;
    }

    // Сохраняем данные пользователя в сессии
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_phone'] = $user['phone'];

    $response['success'] = true;
    echo json_encode($response);
} catch (Exception $e) {
    $response['error'] = 'Ошибка сервера: ' . $e->getMessage();
    echo json_encode($response);
}
?>
<?php
header('Content-Type: application/json');

require_once '../db_connect.php';

$response = ['success' => false, 'error' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';

    // Валидация
    if (empty($name) || empty($phone) || empty($password)) {
        $response['error'] = 'Все поля обязательны';
        echo json_encode($response);
        exit;
    }

    if (!preg_match('/^\+7\d{10}$/', $phone)) {
        $response['error'] = 'Некорректный формат номера телефона';
        echo json_encode($response);
        exit;
    }

    if (strlen($password) < 6) {
        $response['error'] = 'Пароль должен содержать минимум 6 символов';
        echo json_encode($response);
        exit;
    }

    if (strlen($name) < 2) {
        $response['error'] = 'Имя должно содержать минимум 2 символа';
        echo json_encode($response);
        exit;
    }

    // Проверяем, существует ли пользователь с таким номером телефона
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        $response['error'] = 'Пользователь с таким номером телефона уже зарегистрирован';
        echo json_encode($response);
        exit;
    }

    // Хешируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Вставляем нового пользователя
    $stmt = $pdo->prepare("INSERT INTO users (name, phone, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $phone, $hashed_password]);

    $response['success'] = true;
    echo json_encode($response);
} catch (Exception $e) {
    $response['error'] = 'Ошибка сервера: ' . $e->getMessage();
    echo json_encode($response);
}
?>
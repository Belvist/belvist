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
    $product_id = $data['product_id'] ?? 0;

    if ($product_id <= 0) {
        $response['error'] = 'Некорректный ID товара';
        echo json_encode($response);
        exit;
    }

    // Проверяем, существует ли товар
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        $response['error'] = 'Товар не найден';
        echo json_encode($response);
        exit;
    }

    // Проверяем, есть ли уже товар в корзине
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        // Увеличиваем количество
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
    } else {
        // Добавляем новый товар в корзину
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
    }

    $response['success'] = true;
    echo json_encode($response);
} catch (Exception $e) {
    $response['error'] = 'Ошибка сервера: ' . $e->getMessage();
    echo json_encode($response);
}
?>
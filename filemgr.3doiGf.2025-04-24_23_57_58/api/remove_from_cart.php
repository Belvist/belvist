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

    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);

    $response['success'] = true;
    echo json_encode($response);
} catch (Exception $e) {
    $response['error'] = 'Ошибка сервера: ' . $e->getMessage();
    echo json_encode($response);
}
?>
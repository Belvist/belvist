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

    $stmt = $pdo->prepare("SELECT c.product_id, c.quantity, p.title, p.price, p.images
                           FROM cart c
                           JOIN products p ON c.product_id = p.id
                           WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        $item['images'] = json_decode($item['images'], true) ?? [];
        $item['image'] = !empty($item['images']) ? $item['images'][0] : 'https://belvistseller.ru/api/images/products/6802f5151a094.png';
    }

    $response['success'] = true;
    $response['items'] = $items;
    echo json_encode($response);
} catch (Exception $e) {
    $response['error'] = 'Ошибка сервера: ' . $e->getMessage();
    echo json_encode($response);
}
?>
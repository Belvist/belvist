<?php
header('Content-Type: application/json');
session_start();

// Подключение к базе данных
$host = 'localhost';
$dbname = 'u3097720_default';
$username = 'u3097720_default';
$password = 'KN886oVZfIN3tqj4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()]);
    exit;
}

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Пользователь не авторизован']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT p.id AS product_id, p.title, p.price, p.images, p.category
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Форматируем данные
    $formatted_items = [];
    foreach ($items as $item) {
        // Декодируем JSON из поля images
        $images = json_decode($item['images'], true);
        if (!is_array($images)) {
            $images = [$item['images']]; // Если не массив, считаем это одиночной ссылкой
        }
        // Фильтруем пустые или некорректные ссылки
        $images = array_filter($images, function($img) {
            return !empty($img) && filter_var($img, FILTER_VALIDATE_URL);
        });

        $formatted_items[] = [
            'product_id' => $item['product_id'],
            'title' => $item['title'],
            'price' => (float)$item['price'],
            'images' => $images, // Используем ссылки напрямую
            'category' => $item['category'] ?: 'Игрушки'
        ];
    }

    echo json_encode(['success' => true, 'items' => $formatted_items]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка запроса к базе данных: ' . $e->getMessage()]);
    exit;
}
?>
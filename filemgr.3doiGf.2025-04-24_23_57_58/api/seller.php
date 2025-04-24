<?php
header('Content-Type: application/json');

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

$seller_id = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : null;

if (!$seller_id) {
    echo json_encode(['success' => false, 'error' => 'Не указан seller_id']);
    exit;
}

try {
    // Получаем информацию о продавце (убрали description из запроса)
    $stmt = $pdo->prepare("SELECT id, name FROM sellers WHERE id = ?");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        echo json_encode(['success' => false, 'error' => 'Продавец не найден']);
        exit;
    }

    // Получаем товары продавца
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.price, p.images, p.category, p.characteristics,
               s.id AS seller_id, s.name AS seller_name
        FROM products p
        JOIN sellers s ON p.seller_id = s.id
        WHERE p.seller_id = ?
    ");
    $stmt->execute([$seller_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Форматируем данные
    $formatted_products = [];
    foreach ($products as $product) {
        // Декодируем JSON из поля images
        $images = json_decode($product['images'], true);
        if (!is_array($images)) {
            $images = [$product['images']];
        }
        // Фильтруем пустые или некорректные ссылки
        $images = array_filter($images, function($img) {
            return !empty($img) && filter_var($img, FILTER_VALIDATE_URL);
        });

        $formatted_products[] = [
            'id' => $product['id'],
            'title' => $product['title'],
            'price' => (float)$product['price'],
            'images' => $images,
            'category' => $product['category'] ?: 'Игрушки',
            'characteristics' => $product['characteristics'] ?: 'Нет характеристик',
            'seller_id' => $product['seller_id'],
            'seller_name' => $product['seller_name'],
            'seller_icon' => '/api/images/default.png' // Обновленный путь к заглушке
        ];
    }

    echo json_encode([
        'success' => true,
        'seller' => [
            'id' => $seller['id'],
            'name' => $seller['name'],
            'icon' => '/api/images/default.png', // Обновленный путь к заглушке
            'description' => 'Продавец на Belvist' // Устанавливаем значение по умолчанию
        ],
        'products' => $formatted_products
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка запроса к базе данных: ' . $e->getMessage()]);
    exit;
}
?>
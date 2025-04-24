<?php
header('Content-Type: application/json');

function sendResponse($success, $data = [], $error = null) {
    $response = ["success" => $success];
    if ($success) {
        $response["products"] = $data;
    } else {
        $response["error"] = $error;
    }
    echo json_encode($response);
    exit();
}

$servername = "localhost";
$username = "u3097720_default";
$password = "KN886oVZfIN3tqj4";
$dbname = "u3097720_default";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    sendResponse(false, [], "Connection failed: " . $conn->connect_error);
}

$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

$sql = "SELECT p.id, p.images, p.title, p.price, p.category, p.characteristics, 
               p.seller_id, s.name AS seller_name 
        FROM products p 
        LEFT JOIN sellers s ON p.seller_id = s.id";
if ($query) {
    $sql .= " WHERE p.title LIKE '%$query%'";
}

$result = $conn->query($sql);

if ($result === false) {
    sendResponse(false, [], "Query failed: " . $conn->error);
}

$baseImageUrl = "https://belvistseller.ru/api/images/products/";
$products = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['images'])) {
        $decodedImages = json_decode($row['images'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $row['images'] = array_map(function($image) use ($baseImageUrl) {
                if (preg_match('/^https?:\/\//', $image)) {
                    return $image;
                }
                return $baseImageUrl . $image;
            }, $decodedImages);
        } else {
            $row['images'] = [];
        }
    } else {
        $row['images'] = [];
    }
    $row['seller_icon'] = "";
    $products[] = $row;
}

sendResponse(true, $products);

$conn->close();
?>
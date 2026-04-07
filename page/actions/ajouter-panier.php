<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/site.php';

header('Content-Type: text/plain; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo '0';
    exit;
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

if ($productId < 1) {
    http_response_code(400);
    echo '0';
    exit;
}

try {
    $product = fetch_product_by_id($productId);

    if ($product === null || !$product['in_stock']) {
        http_response_code(404);
        echo (string) cart_items_count();
        exit;
    }

    echo (string) add_to_cart($productId, $quantity, (int) $product['stock']);
} catch (Throwable $exception) {
    http_response_code(500);
    echo (string) cart_items_count();
}

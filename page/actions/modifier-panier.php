<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/site.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Methode non autorisee.']);
    exit;
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

if ($productId < 1) {
    http_response_code(400);
    echo json_encode(['message' => 'Produit invalide.']);
    exit;
}

try {
    $product = fetch_product_by_id($productId);

    if ($product === null) {
        http_response_code(404);
        echo json_encode(['message' => 'Bougie introuvable.']);
        exit;
    }

    $safeQuantity = max(0, min($quantity, (int) $product['stock']));
    set_cart_quantity($productId, $safeQuantity);

    $message = $safeQuantity === 0
        ? 'Bougie retiree du panier.'
        : ($safeQuantity < $quantity
            ? 'Quantite ajustee au stock disponible.'
            : 'Quantite du panier mise a jour.');

    echo json_encode(build_cart_update_payload(
        $product,
        $safeQuantity,
        $safeQuantity === 0,
        $message
    ));
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['message' => 'Impossible de mettre a jour le panier pour le moment.']);
}

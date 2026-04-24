<?php
declare(strict_types=1);

// Action appelee en AJAX quand on clique sur "Ajouter au panier".
require_once __DIR__ . '/../includes/site.php';

header('Content-Type: text/plain; charset=UTF-8');

// Cette page accepte uniquement les requetes POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo '0';
    exit;
}

// On nettoie les donnees recues depuis le navigateur.
$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

if ($productId < 1) {
    http_response_code(400);
    echo '0';
    exit;
}

try {
    $product = fetch_product_by_id($productId);

    // Si le produit n'existe pas ou n'est plus en stock, on ne l'ajoute pas.
    if ($product === null || !$product['in_stock']) {
        http_response_code(404);
        echo (string) cart_items_count();
        exit;
    }

    // On renvoie le nouveau nombre total d'articles dans le panier.
    echo (string) add_to_cart($productId, $quantity, (int) $product['stock']);
} catch (Throwable $exception) {
    http_response_code(500);
    echo (string) cart_items_count();
}

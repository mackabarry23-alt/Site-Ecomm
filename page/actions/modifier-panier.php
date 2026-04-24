<?php
declare(strict_types=1);

// Action AJAX pour changer la quantite d'une bougie deja presente dans le panier.
require_once __DIR__ . '/../includes/site.php';

header('Content-Type: application/json; charset=UTF-8');

// On refuse les appels qui ne sont pas en POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Methode non autorisee.']);
    exit;
}

// On convertit les donnees recues en nombres.
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

    // La quantite finale doit rester entre 0 et le stock disponible.
    $safeQuantity = max(0, min($quantity, (int) $product['stock']));
    set_cart_quantity($productId, $safeQuantity);

    // Message envoye au front pour expliquer ce qu'il s'est passe.
    $message = $safeQuantity === 0
        ? 'Bougie retiree du panier.'
        : ($safeQuantity < $quantity
            ? 'Quantite ajustee au stock disponible.'
            : 'Quantite du panier mise a jour.');

    // On renvoie un resume complet pour que le JavaScript mette la page a jour.
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

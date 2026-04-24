<?php
declare(strict_types=1);

// Action finale : validation du formulaire de livraison puis creation de la commande.
require_once __DIR__ . '/../includes/site.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('page/livraison.php'), true, 303);
    exit;
}

// On nettoie puis on memorise les donnees pour reafficher le formulaire si besoin.
$delivery = sanitize_delivery_form_input($_POST);
store_delivery_form_data($delivery);

// Avant de creer la commande, on reverifie le panier.
$issues = cart_validation_issues();

if ($issues !== []) {
    set_flash_message('error', $issues[0]);
    header('Location: ' . app_url('page/panier.php'), true, 303);
    exit;
}

// On verifie ensuite le formulaire lui-meme.
$deliveryErrors = validate_delivery_form_data($delivery);

if ($deliveryErrors !== []) {
    set_flash_message('error', $deliveryErrors[0]);
    header('Location: ' . app_url('page/livraison.php'), true, 303);
    exit;
}

try {
    // Si tout est bon, on cree la commande puis on redirige vers la confirmation.
    $orderId = create_order_from_cart($delivery);
    set_flash_message('success', 'Commande confirmee avec succes.');
    header('Location: ' . app_url('page/confirmation-commande.php?id=' . $orderId), true, 303);
    exit;
} catch (Throwable $exception) {
    // En cas d'erreur, on garde le formulaire et on informe l'utilisateur.
    set_flash_message('error', $exception->getMessage());
    header('Location: ' . app_url('page/livraison.php'), true, 303);
    exit;
}

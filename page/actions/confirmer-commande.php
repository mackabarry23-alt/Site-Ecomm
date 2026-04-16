<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/site.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('page/livraison.php'), true, 303);
    exit;
}

$delivery = sanitize_delivery_form_input($_POST);
store_delivery_form_data($delivery);

$issues = cart_validation_issues();

if ($issues !== []) {
    set_flash_message('error', $issues[0]);
    header('Location: ' . app_url('page/panier.php'), true, 303);
    exit;
}

$deliveryErrors = validate_delivery_form_data($delivery);

if ($deliveryErrors !== []) {
    set_flash_message('error', $deliveryErrors[0]);
    header('Location: ' . app_url('page/livraison.php'), true, 303);
    exit;
}

try {
    $orderId = create_order_from_cart($delivery);
    set_flash_message('success', 'Commande confirmee avec succes.');
    header('Location: ' . app_url('page/confirmation-commande.php?id=' . $orderId), true, 303);
    exit;
} catch (Throwable $exception) {
    set_flash_message('error', $exception->getMessage());
    header('Location: ' . app_url('page/livraison.php'), true, 303);
    exit;
}

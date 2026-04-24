<?php
declare(strict_types=1);

// Action simple : on verifie le panier puis on redirige.
require_once __DIR__ . '/../includes/site.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('page/panier.php'), true, 303);
    exit;
}

// On controle que le panier est encore coherent avant la livraison.
$issues = cart_validation_issues();

if ($issues !== []) {
    set_flash_message('error', $issues[0]);
    header('Location: ' . app_url('page/panier.php'), true, 303);
    exit;
}

set_flash_message('success', 'Panier valide. Indique maintenant tes informations de livraison.');
header('Location: ' . app_url('page/livraison.php'), true, 303);
exit;

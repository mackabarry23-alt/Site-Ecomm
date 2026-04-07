<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/site.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /page/panier.php', true, 303);
    exit;
}

$issues = cart_validation_issues();

if ($issues !== []) {
    set_flash_message('error', $issues[0]);
    header('Location: /page/panier.php', true, 303);
    exit;
}

set_flash_message('success', 'Panier valide. Indique maintenant tes informations de livraison.');
header('Location: /page/livraison.php', true, 303);
exit;

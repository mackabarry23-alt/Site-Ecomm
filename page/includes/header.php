<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? "Lueur d'Ambre";
$metaDescription = $metaDescription ?? "Boutique de bougies parfumees aux ambiances douces et chaleureuses.";
$activePage = $activePage ?? '';
$cartCount = cart_items_count();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= escape($pageTitle) ?></title>
  <meta name="description" content="<?= escape($metaDescription) ?>" />
  <link rel="icon" href="<?= escape(app_url('image/favicon.svg')) ?>" type="image/svg+xml" />
  <link rel="stylesheet" href="<?= escape(app_url('css/style-premium-bougies.css')) ?>" />
</head>
<body>
  <header>
    <div class="container">
      <nav>
        <a class="logo" href="<?= escape(app_url('page/index.php')) ?>">Lueur d'Ambre</a>
        <ul class="nav-links">
          <li><a href="<?= escape(app_url('page/index.php')) ?>"<?= $activePage === 'home' ? ' class="active" aria-current="page"' : '' ?>>Accueil</a></li>
          <li><a href="<?= escape(app_url('page/catalogue-bougies.php')) ?>"<?= $activePage === 'catalog' ? ' class="active" aria-current="page"' : '' ?>>Catalogue</a></li>
          <li>
            <a class="cart-link<?= $activePage === 'cart' ? ' active' : '' ?>" href="<?= escape(app_url('page/panier.php')) ?>"<?= $activePage === 'cart' ? ' aria-current="page"' : '' ?>>
              Panier
              <span class="cart-count" data-cart-count><?= $cartCount ?></span>
            </a>
          </li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <div class="cart-toast" data-cart-feedback hidden aria-live="polite"></div>

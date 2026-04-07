<?php
declare(strict_types=1);
?>
<article class="product-card">
  <a class="product-link" href="/page/produit-bougie.php?id=<?= (int) $product['id'] ?>">
    <img class="product-img" src="<?= escape($product['image']) ?>" alt="<?= escape($product['nom']) ?>" />
  </a>

  <div class="product-info">
    <div class="product-badges">
      <span class="badge <?= $product['in_stock'] ? 'badge-stock' : 'badge-oos' ?>">
        <?= $product['in_stock'] ? 'En stock' : 'Rupture' ?>
      </span>
      <span class="badge"><?= escape($product['category_label']) ?></span>
    </div>

    <div class="product-name"><?= escape($product['nom']) ?></div>
    <div class="product-price"><?= format_price((float) $product['prix_ht']) ?></div>
    <div class="product-description"><?= escape($product['description_courte'] ?: $product['lead']) ?></div>
    <div class="product-actions">
      <a class="btn btn-ghost" href="/page/produit-bougie.php?id=<?= (int) $product['id'] ?>">Decouvrir la bougie</a>
      <button
        class="btn btn-add-cart"
        type="button"
        data-default-label="Ajouter au panier"
        onclick="addToCart(<?= (int) $product['id'] ?>, 1, this)"
        <?= $product['in_stock'] ? '' : 'disabled aria-disabled="true"' ?>
      >
        <?= $product['in_stock'] ? 'Ajouter au panier' : 'Indisponible' ?>
      </button>
    </div>
  </div>
</article>

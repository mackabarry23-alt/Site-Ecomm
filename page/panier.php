<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site.php';

$pageTitle = "Panier - Lueur d'Ambre";
$metaDescription = "Retrouve les bougies ajoutees a ton panier.";
$activePage = 'cart';
$flash = pull_flash_message();
$cartLines = fetch_cart_lines();
$cartCount = cart_items_count();
$cartTotal = cart_total_price();
$cartTotalTvac = cart_total_price_tvac();
$cartCountLabel = format_cart_count_label($cartCount);

require __DIR__ . '/includes/header.php';
?>
<main>
  <section class="page-hero">
    <div class="container">
      <span class="eyebrow">Panier parfume</span>
      <h1>Ton panier de bougies</h1>
      <p>Retrouve ici les bougies ajoutees avec le bouton panier et poursuis ta selection en douceur.</p>
    </div>
  </section>

  <section class="products">
    <div class="container page-grid cart-grid">
      <section>
        <?php if ($flash !== null): ?>
          <article class="flash-banner flash-<?= escape($flash['type']) ?>">
            <p><?= escape((string) $flash['message']) ?></p>
          </article>
        <?php endif; ?>

        <div class="list-header">
          <div>
            <h2 class="section-title-sm">Bougies selectionnees</h2>
            <p class="lead">Modifie les quantites directement ici pour affiner ta future commande.</p>
          </div>
          <div class="list-meta" data-cart-count-label><?= escape($cartCountLabel) ?></div>
        </div>

        <article class="detail empty-state" data-cart-empty<?= $cartLines !== [] ? ' hidden' : '' ?>>
          <h3>Ton panier est vide</h3>
          <p>Ajoute une bougie vanillee, florale ou boisee pour commencer ta selection.</p>
          <a class="btn" href="<?= escape(app_url('page/catalogue-bougies.php')) ?>">Explorer les bougies</a>
        </article>

        <div class="cart-lines" data-cart-lines<?= $cartLines === [] ? ' hidden' : '' ?>>
          <?php foreach ($cartLines as $line): ?>
            <?php $product = $line['product']; ?>
            <article class="detail cart-line" data-cart-line="<?= (int) $product['id'] ?>">
              <img class="cart-line-image" src="<?= escape($product['image']) ?>" alt="<?= escape($product['nom']) ?>" />
              <div class="cart-line-content">
                <div class="product-name"><?= escape($product['nom']) ?></div>
                <p class="cart-line-meta"><?= escape($product['description_courte'] ?: $product['lead']) ?></p>
                <p class="cart-line-meta">Prix unitaire : <?= format_price((float) $product['prix_ht']) ?></p>

                <div class="cart-line-controls">
                  <label class="filter-label" for="cart-quantity-<?= (int) $product['id'] ?>">Quantite</label>
                  <div class="cart-quantity-row">
                    <button
                      class="qty-adjust"
                      type="button"
                      data-cart-control="<?= (int) $product['id'] ?>"
                      onclick="changeCartQuantity(<?= (int) $product['id'] ?>, -1)"
                    >
                      -
                    </button>
                    <input
                      id="cart-quantity-<?= (int) $product['id'] ?>"
                      class="input cart-qty-input"
                      type="number"
                      min="0"
                      max="<?= (int) $product['stock'] ?>"
                      value="<?= (int) $line['quantity'] ?>"
                      data-cart-input="<?= (int) $product['id'] ?>"
                      data-current-value="<?= (int) $line['quantity'] ?>"
                      data-cart-control="<?= (int) $product['id'] ?>"
                      onchange="submitCartQuantity(<?= (int) $product['id'] ?>, this.value)"
                    />
                    <button
                      class="qty-adjust"
                      type="button"
                      data-cart-control="<?= (int) $product['id'] ?>"
                      onclick="changeCartQuantity(<?= (int) $product['id'] ?>, 1)"
                    >
                      +
                    </button>
                  </div>
                  <p class="cart-line-meta">Stock disponible : <?= (int) $product['stock'] ?></p>
                </div>
              </div>
              <div class="cart-line-side">
                <div class="cart-line-total" data-line-total="<?= (int) $product['id'] ?>"><?= format_price((float) $line['line_total']) ?></div>
                <button
                  class="btn btn-ghost btn-remove-line"
                  type="button"
                  data-cart-control="<?= (int) $product['id'] ?>"
                  onclick="submitCartQuantity(<?= (int) $product['id'] ?>, 0)"
                >
                  Retirer
                </button>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <aside class="detail cart-summary">
        <h2>Resume</h2>
        <div class="summary-row">
          <span>Articles</span>
          <strong data-cart-count><?= $cartCount ?></strong>
        </div>
        <div class="summary-row">
          <span>Total HT</span>
          <strong data-cart-total><?= format_price($cartTotal) ?></strong>
        </div>
        <div class="summary-row">
          <span>Total estime TVAC</span>
          <strong data-cart-total-tvac><?= format_price($cartTotalTvac) ?></strong>
        </div>
        <p class="summary-note">La validation du panier t'envoie vers la page de livraison pour finaliser la commande.</p>
        <form class="summary-form" action="<?= escape(app_url('page/actions/valider-panier.php')) ?>" method="post">
          <button class="btn" type="submit" data-checkout-button<?= $cartLines === [] ? ' disabled aria-disabled="true"' : '' ?>>Valider mon panier</button>
        </form>
        <a class="btn" href="<?= escape(app_url('page/catalogue-bougies.php')) ?>">Continuer mes achats</a>
      </aside>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

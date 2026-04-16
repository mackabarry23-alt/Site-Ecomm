<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site.php';

$dbError = null;
$product = null;
$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

try {
    if ($productId !== null && $productId !== false) {
        $product = fetch_product_by_id((int) $productId);
    } else {
        $featured = fetch_featured_products(1);
        $product = $featured[0] ?? null;
    }
} catch (Throwable $exception) {
    http_response_code(500);
    $dbError = "Impossible d'afficher cette bougie pour le moment.";
}

if ($dbError === null && $product === null) {
    http_response_code(404);
}

$pageTitle = $product !== null
    ? $product['nom'] . " - Lueur d'Ambre"
    : "Bougie introuvable - Lueur d'Ambre";
$metaDescription = $product !== null
    ? (string) ($product['description_courte'] ?: $product['lead'])
    : "Decouvre une bougie parfumee Lueur d'Ambre.";
$activePage = 'catalog';

require __DIR__ . '/includes/header.php';
?>
<main class="container product-page">
  <?php if ($dbError !== null): ?>
    <article class="detail status-panel">
      <h3>Boutique indisponible</h3>
      <p><?= escape($dbError) ?></p>
    </article>
  <?php elseif ($product === null): ?>
    <article class="detail empty-state">
      <h3>Bougie introuvable</h3>
      <p>La bougie demandee n'est pas disponible. Retourne au <a href="<?= escape(app_url('page/catalogue-bougies.php')) ?>">catalogue</a> pour choisir une autre senteur.</p>
    </article>
  <?php else: ?>
    <nav class="breadcrumb" aria-label="Fil d'Ariane">
      <a href="<?= escape(app_url('page/index.php')) ?>">Accueil</a>
      <span>/</span>
      <a href="<?= escape(app_url('page/catalogue-bougies.php')) ?>">Catalogue</a>
      <span>/</span>
      <span><?= escape($product['nom']) ?></span>
    </nav>

    <section class="product-layout">
      <div class="gallery">
        <img class="gallery-main" src="<?= escape($product['image']) ?>" alt="<?= escape($product['nom']) ?>" />
        <?php if ($product['gallery'] !== []): ?>
          <div class="gallery-thumbs">
            <?php foreach ($product['gallery'] as $galleryImage): ?>
              <img src="<?= escape($galleryImage) ?>" alt="<?= escape($product['nom']) ?>" />
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="product-summary">
        <div class="product-subtitle">Collection <?= escape(strtolower($product['category_label'])) ?></div>
        <h1 class="product-title"><?= escape($product['nom']) ?></h1>

        <div class="product-badges">
          <span class="badge <?= $product['in_stock'] ? 'badge-stock' : 'badge-oos' ?>">
            <?= $product['in_stock'] ? 'En stock' : 'Rupture' ?>
          </span>
          <span class="badge"><?= escape($product['category_label']) ?></span>
          <span class="badge"><?= escape($product['weight']) ?></span>
        </div>

        <div class="price-row">
          <div class="price"><?= format_price((float) $product['prix_ht']) ?></div>
          <div class="list-meta"><?= (int) $product['stock'] ?> exemplaire<?= (int) $product['stock'] > 1 ? 's' : '' ?> disponibles</div>
        </div>

        <p class="lead"><?= escape($product['lead']) ?></p>

        <div class="buy-box">
          <label for="quantity" class="filter-label">Quantite</label>
          <div class="qty-row">
            <input id="quantity" class="input" type="number" min="1" max="<?= max(1, (int) $product['stock']) ?>" value="1" />
            <button
              class="btn btn-buy"
              type="button"
              data-default-label="Ajouter au panier"
              onclick="addToCartFromInput(<?= (int) $product['id'] ?>, 'quantity', this)"
              <?= $product['in_stock'] ? '' : 'disabled aria-disabled="true"' ?>
            >
              <?= $product['in_stock'] ? 'Ajouter au panier' : 'Indisponible' ?>
            </button>
          </div>
          <a class="btn btn-ghost btn-browse" href="<?= escape(app_url('page/catalogue-bougies.php')) ?>">Voir toutes les bougies</a>
        </div>

        <ul class="bullets">
          <li>Temps de combustion estime : <?= escape($product['burn_time']) ?></li>
          <li>Format : <?= escape($product['weight']) ?></li>
          <li>Disponibilite : <?= $product['in_stock'] ? 'commande possible' : 'stock a reapprovisionner' ?></li>
        </ul>
      </div>
    </section>

    <section class="details">
      <article class="detail">
        <h3>Description</h3>
        <p><?= escape($product['description_longue'] ?: $product['description_courte']) ?></p>
      </article>

      <article class="detail">
        <h3>Stock actuel</h3>
        <p>
          <?= $product['in_stock']
            ? 'Cette bougie est disponible a la commande et prete a diffuser sa senteur chez toi.'
            : 'Cette bougie reviendra bientot en stock. Garde-la en tete pour ta prochaine ambiance parfumee.' ?>
        </p>
      </article>

      <article class="detail">
        <h3>Conseil de combustion</h3>
        <p>Laisse bruler la bougie jusqu'a ce que toute la surface soit fondue lors de la premiere utilisation afin d'obtenir une combustion reguliere.</p>
      </article>
    </section>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

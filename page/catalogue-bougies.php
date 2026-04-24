<?php
declare(strict_types=1);

// On charge les fonctions communes du projet.
require_once __DIR__ . '/includes/site.php';

// Variables attendues par le header.
$pageTitle = "Nos bougies - Lueur d'Ambre";
$metaDescription = "Parcours nos bougies parfumees et trouve la senteur qui correspond a ton interieur.";
$activePage = 'catalog';
$filters = null;
$products = [];
$productCount = 0;
$dbError = null;

// On recupere les filtres de l'URL puis les produits correspondants.
try {
    $filters = get_catalog_filters();
    $products = filter_products(fetch_all_products(), $filters);
    $productCount = count($products);
} catch (Throwable $exception) {
    http_response_code(500);
    $dbError = "Impossible d'afficher le catalogue de bougies pour le moment.";
}

require __DIR__ . '/includes/header.php';
?>
<main>
  <section class="page-hero">
    <div class="container">
      <span class="eyebrow">Toutes nos collections</span>
      <h1>Nos bougies parfumees</h1>
      <p>Filtre par senteur, famille olfactive ou budget pour trouver la bougie parfaite pour ton ambiance.</p>
    </div>
  </section>

  <section class="products">
    <div class="container page-grid">
      <aside class="filters" aria-label="Filtres bougies">
        <div class="filters-header">
          <h2>Affiner les bougies</h2>
          <span class="list-meta">Senteurs</span>
        </div>

        <?php if ($dbError === null && $filters !== null): ?>
          <form method="get" action="<?= escape(app_url('page/catalogue-bougies.php')) ?>">
            <div class="filter-group">
              <label class="filter-label" for="q">Recherche</label>
              <input id="q" class="input" type="search" name="q" value="<?= escape($filters['q']) ?>" placeholder="Ex. vanille, lavande..." />
            </div>

            <div class="filter-group">
              <span class="filter-label">Categorie</span>
              <div class="chips">
                <?php foreach (catalog_categories() as $slug => $label): ?>
                  <label class="chip">
                    <input type="radio" name="category" value="<?= escape($slug) ?>"<?= $filters['category'] === $slug ? ' checked' : '' ?> />
                    <?= escape($label) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="filter-group">
              <span class="filter-label">Disponibilite</span>
              <label class="toggle">
                <input type="checkbox" name="in_stock" value="1"<?= $filters['in_stock'] ? ' checked' : '' ?> />
                <span>Afficher uniquement les bougies en stock</span>
              </label>
            </div>

            <div class="filter-group">
              <label class="filter-label" for="max_price">Prix maximum</label>
              <input id="max_price" class="input" type="number" min="1" step="0.10" name="max_price" value="<?= number_format((float) $filters['max_price'], 2, '.', '') ?>" />
              <div class="range-meta">
                <span>Jusqu'a</span>
                <strong><?= format_price((float) $filters['max_available']) ?></strong>
              </div>
            </div>

            <div class="filter-group">
              <label class="filter-label" for="sort">Trier</label>
              <select id="sort" class="input" name="sort">
                <option value="popular"<?= $filters['sort'] === 'popular' ? ' selected' : '' ?>>Nos favorites</option>
                <option value="priceAsc"<?= $filters['sort'] === 'priceAsc' ? ' selected' : '' ?>>Prix croissant</option>
                <option value="priceDesc"<?= $filters['sort'] === 'priceDesc' ? ' selected' : '' ?>>Prix decroissant</option>
                <option value="nameAsc"<?= $filters['sort'] === 'nameAsc' ? ' selected' : '' ?>>Nom A a Z</option>
              </select>
            </div>

            <div class="filter-actions">
              <button class="btn" type="submit">Appliquer</button>
              <a class="btn btn-ghost" href="<?= escape(app_url('page/catalogue-bougies.php')) ?>">Reinitialiser</a>
            </div>
          </form>
        <?php else: ?>
          <article class="detail status-panel">
            <h3>Boutique indisponible</h3>
            <p><?= escape($dbError ?? 'Une erreur est survenue.') ?></p>
          </article>
        <?php endif; ?>
      </aside>

      <section>
        <div class="list-header">
          <div>
            <h2 class="section-title-sm">Toutes les bougies</h2>
            <p class="lead">Des bougies pensees pour parfumer l'interieur avec elegance, douceur et chaleur.</p>
          </div>
          <div class="list-meta"><?= $productCount ?> bougie<?= $productCount > 1 ? 's' : '' ?></div>
        </div>

        <?php if ($dbError === null && $products === []): ?>
          <article class="detail empty-state">
            <h3>Aucune bougie trouvee</h3>
            <p>Essaie d'assouplir les filtres pour retrouver une senteur ou un format qui te convient.</p>
          </article>
        <?php elseif ($dbError === null): ?>
          <div class="products-grid">
            <?php foreach ($products as $product): ?>
              <?php require __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

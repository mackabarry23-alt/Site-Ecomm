<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site.php';

$pageTitle = "Lueur d'Ambre - Boutique de bougies";
$metaDescription = "Bougies artisanales parfumees pour creer une ambiance douce et chaleureuse.";
$activePage = 'home';
$featuredProducts = [];
$dbError = null;

try {
    $featuredProducts = fetch_featured_products(3);
} catch (Throwable $exception) {
    http_response_code(500);
    $dbError = "Impossible d'afficher nos bougies pour le moment.";
}

require __DIR__ . '/includes/header.php';
?>
<main>
  <section class="hero" id="accueil">
    <div class="container">
      <span class="eyebrow">Bougies artisanales</span>
      <h1>Des bougies parfumees pour rechauffer chaque piece</h1>
      <p>Bienvenue chez Lueur d'Ambre. Decouvre des bougies aux notes vanillees, florales et boisees pour creer une ambiance apaisante a la maison.</p>
      <div class="hero-actions">
        <a class="btn" href="/page/catalogue-bougies.php">Decouvrir le catalogue</a>
      </div>
    </div>
  </section>

  <section class="products" id="produits">
    <div class="container">
      <div class="section-heading">
        <span class="section-kicker">Selection du moment</span>
        <h2 class="section-title">Les bougies a l'honneur</h2>
        <p class="section-intro">Des senteurs choisies pour envelopper le salon, la chambre ou un coin lecture d'une lumiere douce et parfumee.</p>
      </div>

      <?php if ($dbError !== null): ?>
        <article class="detail status-panel">
          <h3>Boutique indisponible</h3>
          <p><?= escape($dbError) ?></p>
        </article>
      <?php elseif ($featuredProducts === []): ?>
        <article class="detail empty-state">
          <h3>Aucune bougie disponible</h3>
          <p>Notre selection se refait une beaute. Reviens bientot pour decouvrir de nouvelles senteurs.</p>
        </article>
      <?php else: ?>
        <div class="products-grid">
          <?php foreach ($featuredProducts as $product): ?>
            <?php require __DIR__ . '/includes/product-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="products">
    <div class="container">
      <div class="details story-grid">
        <article class="detail">
          <h3>Senteurs enveloppantes</h3>
          <p>Vanille, lavande, bois de santal ou fleur de coton: chaque bougie est pensee pour installer une atmosphere chaleureuse.</p>
        </article>
        <article class="detail">
          <h3>Formats pour chaque moment</h3>
          <p>Petite, moyenne ou grande, choisis la bougie ideale pour un instant cocooning, un diner ou une soiree detente.</p>
        </article>
        <article class="detail">
          <h3>Maison parfumee</h3>
          <p>Nos bougies accompagnent les rituels du quotidien avec une flamme douce, une cire soignee et des parfums reconfortants.</p>
        </article>
      </div>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

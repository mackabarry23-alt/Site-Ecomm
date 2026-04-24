<?php
declare(strict_types=1);

// On charge les fonctions communes du projet.
require_once __DIR__ . '/includes/site.php';

// On lit le numero de commande depuis l'URL.
$flash = pull_flash_message();
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$order = $orderId !== null && $orderId !== false ? fetch_order_summary((int) $orderId) : null;

if ($order === null) {
    http_response_code(404);
}

// Variables du header HTML.
$pageTitle = $order !== null
    ? 'Commande #' . $order['id'] . " - Lueur d'Ambre"
    : "Commande introuvable - Lueur d'Ambre";
$metaDescription = $order !== null
    ? 'Confirmation de commande pour tes bougies Lueur d\'Ambre.'
    : 'Confirmation de commande indisponible.';
$activePage = 'cart';

require __DIR__ . '/includes/header.php';
?>
<main>
  <section class="page-hero">
    <div class="container">
      <span class="eyebrow">Confirmation</span>
      <h1>Confirmation de commande</h1>
      <p>Le recapitulatif de commande est pret avec les informations de livraison et les totaux enregistres.</p>
    </div>
  </section>

  <section class="products">
    <div class="container confirmation-layout">
      <?php if ($flash !== null): ?>
        <article class="flash-banner flash-<?= escape($flash['type']) ?>">
          <p><?= escape((string) $flash['message']) ?></p>
        </article>
      <?php endif; ?>

      <?php if ($order === null): ?>
        <article class="detail empty-state">
          <h3>Commande introuvable</h3>
          <p>Le recapitulatif demande n'existe pas ou n'est plus accessible.</p>
          <a class="btn" href="<?= escape(app_url('page/catalogue-bougies.php')) ?>">Retourner au catalogue</a>
        </article>
      <?php else: ?>
        <section class="confirmation-grid">
          <article class="detail confirmation-card">
            <span class="section-kicker">Numero</span>
            <h2 class="section-title-sm">Commande #<?= (int) $order['id'] ?></h2>
            <p class="confirmation-note">Commande enregistree le <?= escape((string) $order['date_creation']) ?>.</p>
          </article>

          <article class="detail confirmation-card">
            <span class="section-kicker">Totaux</span>
            <h2 class="section-title-sm"><?= format_price((float) $order['total_tvac']) ?></h2>
            <p class="confirmation-note"><?= (int) $order['items_count'] ?> produit<?= (int) $order['items_count'] > 1 ? 's' : '' ?> dans la commande.</p>
            <p class="confirmation-note">Total HT : <?= format_price((float) $order['total_ht']) ?></p>
          </article>

          <article class="detail confirmation-card">
            <span class="section-kicker">Livraison</span>
            <h2 class="section-title-sm">Adresse enregistree</h2>
            <p class="address-block"><?= nl2br(escape((string) $order['adresse_livraison'])) ?></p>
            <p class="confirmation-note">Email de contact : <?= escape((string) $order['email']) ?></p>
          </article>
        </section>

        <article class="detail confirmation-items">
          <div class="section-heading-left">
            <span class="section-kicker">Recapitulatif</span>
            <h2 class="section-title-sm">Bougies commandees</h2>
          </div>

          <div class="confirmation-item-list">
            <?php foreach ($order['items'] as $item): ?>
              <div class="confirmation-item">
                <div>
                  <div class="checkout-item-name"><?= escape($item['name']) ?></div>
                  <p class="checkout-item-meta"><?= (int) $item['quantity'] ?> x <?= format_price((float) $item['unit_price']) ?></p>
                </div>
                <strong><?= format_price((float) $item['line_total']) ?></strong>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="checkout-actions">
            <a class="btn" href="<?= escape(app_url('page/catalogue-bougies.php')) ?>">Continuer mes achats</a>
            <a class="btn btn-ghost" href="<?= escape(app_url('page/index.php')) ?>">Retour a l'accueil</a>
          </div>
        </article>

        
      <?php endif; ?>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

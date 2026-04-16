<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site.php';

$pageTitle = "Livraison - Lueur d'Ambre";
$metaDescription = "Indique tes informations de livraison pour recevoir tes bougies.";
$activePage = 'cart';
$flash = pull_flash_message();
$delivery = get_delivery_form_data();
$cartLines = fetch_cart_lines();
$cartCount = cart_items_count();
$cartTotal = cart_total_price();
$cartTotalTvac = cart_total_price_tvac();
$cartIssues = cart_validation_issues();

require __DIR__ . '/includes/header.php';
?>
<main>
  <section class="page-hero">
    <div class="container">
      <span class="eyebrow">Livraison</span>
      <h1>Adresse de livraison</h1>
      <p>Renseigne les informations du destinataire pour recevoir ta commande de bougies a la bonne adresse.</p>
    </div>
  </section>

  <section class="products">
    <div class="container checkout-layout">
      <section>
        <?php if ($flash !== null): ?>
          <article class="flash-banner flash-<?= escape($flash['type']) ?>">
            <p><?= escape((string) $flash['message']) ?></p>
          </article>
        <?php endif; ?>

        <?php if ($cartIssues !== []): ?>
          <article class="detail empty-state">
            <h3>Livraison indisponible</h3>
            <p><?= escape($cartIssues[0]) ?></p>
            <a class="btn" href="<?= escape(app_url('page/panier.php')) ?>">Retourner au panier</a>
          </article>
        <?php else: ?>
          <form class="checkout-form" action="<?= escape(app_url('page/actions/confirmer-commande.php')) ?>" method="post">
            <article class="detail form-card">
              <div class="section-heading-left">
                <span class="section-kicker">Commanditaire</span>
                <h2 class="section-title-sm">Informations de contact</h2>
                <p class="section-intro-sm">Ces informations permettent de rattacher la commande a la bonne personne.</p>
              </div>

              <div class="field-grid">
                <div class="field-span">
                  <label class="filter-label" for="email">Email de contact</label>
                  <input id="email" class="input" type="email" name="email" value="<?= escape($delivery['email']) ?>" autocomplete="email" required />
                </div>

                <div>
                  <label class="filter-label" for="nom">Nom</label>
                  <input id="nom" class="input" type="text" name="nom" value="<?= escape($delivery['nom']) ?>" autocomplete="family-name" required />
                </div>

                <div>
                  <label class="filter-label" for="prenom">Prenom</label>
                  <input id="prenom" class="input" type="text" name="prenom" value="<?= escape($delivery['prenom']) ?>" autocomplete="given-name" required />
                </div>
              </div>
            </article>

            <article class="detail form-card">
              <div class="section-heading-left">
                <span class="section-kicker">Expedition</span>
                <h2 class="section-title-sm">Adresse postale</h2>
                <p class="section-intro-sm">Une adresse claire nous permet d'expedier tes bougies sans erreur.</p>
              </div>

              <div class="field-grid">
                <div class="field-span">
                  <label class="filter-label" for="rue">Rue et numero</label>
                  <input id="rue" class="input" type="text" name="rue" value="<?= escape($delivery['rue']) ?>" autocomplete="street-address" required />
                </div>

                <div>
                  <label class="filter-label" for="code_postal">Code postal</label>
                  <input id="code_postal" class="input" type="text" name="code_postal" value="<?= escape($delivery['code_postal']) ?>" autocomplete="postal-code" required />
                </div>

                <div>
                  <label class="filter-label" for="ville">Ville</label>
                  <input id="ville" class="input" type="text" name="ville" value="<?= escape($delivery['ville']) ?>" autocomplete="address-level2" required />
                </div>

                <div class="field-span">
                  <label class="filter-label" for="pays">Pays</label>
                  <select id="pays" class="input" name="pays" autocomplete="country-name" required>
                    <?php foreach (delivery_countries() as $country): ?>
                      <option value="<?= escape($country) ?>"<?= $delivery['pays'] === $country ? ' selected' : '' ?>><?= escape($country) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </article>

            <div class="checkout-actions">
              <button class="btn" type="submit">Confirmer la commande</button>
              <a class="btn btn-ghost" href="<?= escape(app_url('page/panier.php')) ?>">Retour au panier</a>
            </div>
          </form>
        <?php endif; ?>
      </section>

      <aside class="detail checkout-summary">
        <h2>Recapitulatif</h2>
        <div class="summary-row">
          <span>Articles</span>
          <strong><?= $cartCount ?></strong>
        </div>
        <div class="summary-row">
          <span>Total HT</span>
          <strong><?= format_price($cartTotal) ?></strong>
        </div>
        <div class="summary-row">
          <span>Total estime TVAC</span>
          <strong><?= format_price($cartTotalTvac) ?></strong>
        </div>
        <p class="summary-note">TVA estimee a <?= escape(format_percent(vat_rate())) ?>% pour finaliser ton recapitulatif.</p>

        <div class="checkout-items">
          <?php foreach ($cartLines as $line): ?>
            <?php $product = $line['product']; ?>
            <article class="checkout-item">
              <img src="<?= escape($product['image']) ?>" alt="<?= escape($product['nom']) ?>" />
              <div>
                <div class="checkout-item-name"><?= escape($product['nom']) ?></div>
                <p class="checkout-item-meta"><?= (int) $line['quantity'] ?> x <?= format_price((float) $product['prix_ht']) ?></p>
              </div>
              <strong><?= format_price((float) $line['line_total']) ?></strong>
            </article>
          <?php endforeach; ?>
        </div>
      </aside>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

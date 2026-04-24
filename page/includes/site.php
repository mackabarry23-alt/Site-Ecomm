<?php
declare(strict_types=1);

// -----------------------------------------------------------------------------
// SESSION
// -----------------------------------------------------------------------------
// La session PHP sert a memoriser des informations entre deux pages :
// le panier, les messages flash, le formulaire de livraison, etc.
function ensure_session_started(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

ensure_session_started();

// -----------------------------------------------------------------------------
// URLS ET CONFIGURATION GENERALE
// -----------------------------------------------------------------------------
// Cette fonction retrouve le "dossier de base" du projet dans l'URL.
// C'est utile si le site n'est pas installe a la racine du serveur.
function app_base_path(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    // On recupere le chemin du script appele par le navigateur.
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptFilename = realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $projectRoot = realpath(__DIR__ . '/../../');

    if ($scriptName !== '' && $scriptFilename !== false && $projectRoot !== false) {
        $normalizedScriptFilename = str_replace('\\', '/', $scriptFilename);
        $normalizedProjectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');

        if (str_starts_with($normalizedScriptFilename, $normalizedProjectRoot . '/')) {
            $relativeScript = ltrim(substr($normalizedScriptFilename, strlen($normalizedProjectRoot)), '/');

            if ($relativeScript !== '' && str_ends_with($scriptName, $relativeScript)) {
                $basePath = substr($scriptName, 0, -strlen($relativeScript));
            }
        }
    }

    // Si on ne trouve rien, on retombe sur une racine vide.
    if (!is_string($basePath)) {
        $basePath = '';
    }

    $basePath = rtrim($basePath, '/');

    return $basePath === '/' ? '' : $basePath;
}

// Cette fonction construit une URL complete a partir d'un chemin simple.
// Exemple : app_url('page/panier.php')
function app_url(string $path = ''): string
{
    $basePath = app_base_path();
    $cleanPath = ltrim($path, '/');

    if ($cleanPath === '') {
        return $basePath === '' ? '/' : $basePath . '/';
    }

    return ($basePath === '' ? '' : $basePath) . '/' . $cleanPath;
}

// Cette fonction renvoie la connexion PDO a la base de donnees.
// Le fichier config/database.php doit creer une variable $pdo.
function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    require __DIR__ . '/../../config/database.php';

    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException('Connexion PDO introuvable.');
    }

    return $pdo;
}

// Cette fonction protege le HTML contre les caracteres speciaux.
// On l'utilise avant d'afficher du texte venant de la base ou d'un formulaire.
function escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Formate un prix dans le style du site.
function format_price(float $price): string
{
    return number_format($price, 2, ',', ' ') . ' EUR';
}

// -----------------------------------------------------------------------------
// MESSAGES FLASH
// -----------------------------------------------------------------------------
// Un message flash est un message temporaire affiche une seule fois
// apres une redirection (succes, erreur, information...).
function set_flash_message(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flash_message(): ?array
{
    $flash = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);

    return is_array($flash) ? $flash : null;
}

// Libelle lisible pour le nombre d'articles dans le panier.
function format_cart_count_label(int $count): string
{
    return $count . ' article' . ($count > 1 ? 's' : '');
}

// TVA appliquee au projet.
function vat_rate(): float
{
    return 0.21;
}

// Transforme un ratio en pourcentage lisible.
// Exemple : 0.21 devient "21".
function format_percent(float $ratio): string
{
    return (string) round($ratio * 100);
}

// Calcule le total TTC (TVAC) a partir du total HT.
function calculate_total_tvac(float $totalHt): float
{
    return round($totalHt * (1 + vat_rate()), 2);
}

// -----------------------------------------------------------------------------
// PANIER
// -----------------------------------------------------------------------------
// Lit le panier en session. Le panier est stocke sous la forme :
// [idProduit => quantite]
function get_cart(): array
{
    $cart = $_SESSION['panier'] ?? [];

    return is_array($cart) ? $cart : [];
}

// Compte le nombre total d'articles dans le panier.
// Si un produit a une quantite 3, cela compte pour 3 articles.
function cart_items_count(): int
{
    $total = 0;

    foreach (fetch_cart_lines() as $line) {
        $total += (int) $line['quantity'];
    }

    return $total;
}

// Definit la quantite d'un produit dans le panier.
// Si la quantite vaut 0 ou moins, on retire le produit.
function set_cart_quantity(int $productId, int $quantity): int
{
    $cart = get_cart();

    if ($quantity <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $quantity;
    }

    $_SESSION['panier'] = $cart;

    return cart_items_count();
}

// Ajoute une quantite supplementaire au panier.
// On peut limiter la quantite maximale avec le stock disponible.
function add_to_cart(int $productId, int $quantity = 1, ?int $maxQuantity = null): int
{
    $safeQuantity = max(1, $quantity);
    $cart = get_cart();
    $targetQuantity = (int) ($cart[$productId] ?? 0) + $safeQuantity;

    if ($maxQuantity !== null) {
        $targetQuantity = min($targetQuantity, max(0, $maxQuantity));
    }

    return set_cart_quantity($productId, $targetQuantity);
}

// -----------------------------------------------------------------------------
// LIVRAISON
// -----------------------------------------------------------------------------
// Liste des pays disponibles dans le formulaire de livraison.
function delivery_countries(): array
{
    return ['Belgique', 'France', 'Luxembourg', 'Pays-Bas'];
}

// Valeurs par defaut du formulaire de livraison.
function default_delivery_form_data(): array
{
    return [
        'email' => '',
        'nom' => '',
        'prenom' => '',
        'rue' => '',
        'code_postal' => '',
        'ville' => '',
        'pays' => 'Belgique',
    ];
}

// Nettoie les donnees recues depuis le formulaire :
// - supprime les espaces inutiles
// - remet un pays autorise si la valeur est invalide
function sanitize_delivery_form_input(array $input): array
{
    $defaults = default_delivery_form_data();
    $countries = delivery_countries();
    $country = trim((string) ($input['pays'] ?? $defaults['pays']));

    if (!in_array($country, $countries, true)) {
        $country = $defaults['pays'];
    }

    return [
        'email' => trim((string) ($input['email'] ?? $defaults['email'])),
        'nom' => trim((string) ($input['nom'] ?? $defaults['nom'])),
        'prenom' => trim((string) ($input['prenom'] ?? $defaults['prenom'])),
        'rue' => trim((string) ($input['rue'] ?? $defaults['rue'])),
        'code_postal' => trim((string) ($input['code_postal'] ?? $defaults['code_postal'])),
        'ville' => trim((string) ($input['ville'] ?? $defaults['ville'])),
        'pays' => $country,
    ];
}

// Recharge le formulaire depuis la session.
function get_delivery_form_data(): array
{
    $stored = $_SESSION['delivery_form'] ?? [];

    return sanitize_delivery_form_input(is_array($stored) ? $stored : []);
}

// Sauvegarde le formulaire dans la session.
function store_delivery_form_data(array $data): void
{
    $_SESSION['delivery_form'] = sanitize_delivery_form_input($data);
}

// Efface le formulaire une fois la commande terminee.
function clear_delivery_form_data(): void
{
    unset($_SESSION['delivery_form']);
}

// Verifie si les champs obligatoires sont bien remplis.
function validate_delivery_form_data(array $data): array
{
    $delivery = sanitize_delivery_form_input($data);
    $errors = [];

    foreach (['email', 'nom', 'prenom', 'rue', 'code_postal', 'ville', 'pays'] as $field) {
        if ($delivery[$field] === '') {
            $errors[] = 'Tous les champs de livraison sont obligatoires.';
            break;
        }
    }

    if ($delivery['email'] !== '' && filter_var($delivery['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'L\'adresse email n\'est pas valide.';
    }

    return array_values(array_unique($errors));
}

// Construit une adresse multi-ligne a enregistrer dans la commande.
function build_delivery_address_text(array $data): string
{
    $delivery = sanitize_delivery_form_input($data);

    return implode("\n", [
        trim($delivery['prenom'] . ' ' . $delivery['nom']),
        $delivery['rue'],
        trim($delivery['code_postal'] . ' ' . $delivery['ville']),
        $delivery['pays'],
    ]);
}

// -----------------------------------------------------------------------------
// PRODUITS
// -----------------------------------------------------------------------------
// Normalise un texte pour faciliter les recherches :
// - minuscules
// - suppression des accents quand c'est possible
function normalize_text(string $text): string
{
    $normalized = function_exists('mb_strtolower')
        ? mb_strtolower($text, 'UTF-8')
        : strtolower($text);

    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);

    return $ascii !== false ? $ascii : $normalized;
}

// Regroupe les textes importants d'un produit dans une seule chaine.
// Cela evite de reecrire la meme concatenation partout.
function product_search_text(array $product): string
{
    return normalize_text(implode(' ', [
        (string) ($product['nom'] ?? ''),
        (string) ($product['description_courte'] ?? ''),
        (string) ($product['description_longue'] ?? ''),
    ]));
}

// Retourne true des qu'un mot-cle est trouve dans le texte.
function contains_any_keyword(string $text, array $keywords): bool
{
    foreach ($keywords as $keyword) {
        if (str_contains($text, $keyword)) {
            return true;
        }
    }

    return false;
}

// Cette fonction permet de ne garder que les produits qui ressemblent
// vraiment a des bougies dans la base de donnees.
function is_candle_product(array $product): bool
{
    $content = product_search_text($product);

    return contains_any_keyword($content, ['bougie', 'cire', 'meche', 'parfumee', 'senteur', 'combustion']);
}

// Devine une categorie simple a partir du texte du produit.
// Comme la base de donnees ne stocke pas cette info directement,
// on la reconstruit avec des mots-cles.
function infer_product_category(array $product): array
{
    $content = product_search_text($product);

    $categories = [
        'gourmande' => [
            'label' => 'Gourmande',
            'keywords' => ['vanille', 'pomme', 'cannelle', 'caramel', 'gourmand'],
        ],
        'boisee' => [
            'label' => 'Boisee',
            'keywords' => ['bois', 'santal', 'ambre', 'cedre', 'musc'],
        ],
        'florale' => [
            'label' => 'Florale',
            'keywords' => ['lavande', 'fleur', 'rose', 'jasmin'],
        ],
        'fraiche' => [
            'label' => 'Fraiche',
            'keywords' => ['marine', 'coton', 'the', 'blanc', 'brise', 'eucalyptus'],
        ],
    ];

    foreach ($categories as $slug => $category) {
        if (contains_any_keyword($content, $category['keywords'])) {
            return ['slug' => $slug, 'label' => $category['label']];
        }
    }

    return ['slug' => 'signature', 'label' => 'Signature'];
}

// Bibliotheque d'images disponibles pour les differentes senteurs.
function candle_image_library(): array
{
    return [
        'vanille' => [
            'petite' => app_url('image/bougie-vanille-petite.png'),
            'grande' => app_url('image/bougie-vanille-grande.png'),
        ],
        'lavande' => [
            'petite' => app_url('image/bougie-lavande-petite.png'),
            'grande' => app_url('image/bougie-lavande-grande.png'),
        ],
        'bois-de-santal' => [
            'petite' => app_url('image/bougie-bois-de-santal-petite.png'),
        ],
        'fleur-de-coton' => [
            'grande' => app_url('image/bougie-fleur-de-coton-grande.png'),
        ],
    ];
}

// Devine la senteur principale du produit.
function infer_product_scent(array $product): string
{
    $content = product_search_text($product);

    $scents = [
        'vanille' => ['vanille'],
        'lavande' => ['lavande'],
        'bois-de-santal' => ['bois de santal', 'santal'],
        'fleur-de-coton' => ['fleur de coton', 'coton'],
    ];

    foreach ($scents as $slug => $keywords) {
        if (contains_any_keyword($content, $keywords)) {
            return $slug;
        }
    }

    return 'signature';
}

// Devine la taille a partir du nom du produit.
function infer_size_slug(string $productName): string
{
    $name = normalize_text($productName);

    if (str_contains($name, 'petite')) {
        return 'petite';
    }

    if (str_contains($name, 'moyenne')) {
        return 'moyenne';
    }

    if (str_contains($name, 'grande')) {
        return 'grande';
    }

    return 'standard';
}

// Choisit l'image principale du produit.
function pick_product_image(array $product): string
{
    $library = candle_image_library();
    $scent = infer_product_scent($product);
    $size = infer_size_slug((string) ($product['nom'] ?? ''));
    $images = $library[$scent] ?? [];

    if (isset($images[$size])) {
        return $images[$size];
    }

    if ($images !== []) {
        return (string) reset($images);
    }

    $firstFamily = reset($library);

    return (string) reset($firstFamily);
}

// Construit une petite galerie d'images secondaires.
function build_gallery(array $product): array
{
    $primaryImage = pick_product_image($product);
    $library = candle_image_library();
    $scent = infer_product_scent($product);
    $familyImages = array_values($library[$scent] ?? []);
    $gallery = array_values(array_filter(
        array_unique($familyImages),
        static fn (string $image): bool => $image !== $primaryImage
    ));

    return array_slice($gallery, 0, 3);
}

// Devine un poids lisible a partir du nom du produit.
function infer_weight(string $productName): string
{
    $name = normalize_text($productName);

    if (str_contains($name, 'petite')) {
        return '100 g';
    }

    if (str_contains($name, 'moyenne')) {
        return '200 g';
    }

    if (str_contains($name, 'grande')) {
        return '300 g';
    }

    return '220 g';
}

// Associe un temps de combustion au poids.
function infer_burn_time(string $weight): string
{
    return match ($weight) {
        '100 g' => '20 heures',
        '200 g' => '40 heures',
        '300 g' => '60 heures',
        default => '45 heures',
    };
}

// Enrichit un produit brut venant de la base avec des donnees utiles
// pour l'affichage dans les pages.
function enrich_product(array $product): array
{
    $product['id'] = (int) ($product['id'] ?? 0);
    $product['prix_ht'] = (float) ($product['prix_ht'] ?? 0);
    $product['stock'] = (int) ($product['stock'] ?? 0);
    $product['disponible'] = (int) ($product['disponible'] ?? 1);
    $product['priorite'] = (int) ($product['priorite'] ?? 999);

    $category = infer_product_category($product);
    $product['category_slug'] = $category['slug'];
    $product['category_label'] = $category['label'];
    $product['in_stock'] = $product['disponible'] === 1 && $product['stock'] > 0;
    $product['image'] = pick_product_image($product);
    $product['gallery'] = build_gallery($product);
    $product['weight'] = infer_weight((string) $product['nom']);
    $product['burn_time'] = infer_burn_time($product['weight']);
    $product['lead'] = $product['description_longue'] ?: $product['description_courte'];

    return $product;
}

// Recupere tous les produits, puis garde seulement les bougies.
// Le resultat est memorise dans une variable statique pour eviter
// de refaire la requete plusieurs fois dans la meme page.
function fetch_all_products(): array
{
    static $products = null;

    if (is_array($products)) {
        return $products;
    }

    $pdo = get_pdo();
    $stmt = $pdo->query(
        'SELECT id, nom, description_courte, description_longue, prix_ht, stock, disponible, priorite, date_enregistrement
         FROM produit
         ORDER BY priorite ASC, nom ASC'
    );

    $rows = array_values(array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), 'is_candle_product'));
    $products = array_map('enrich_product', $rows);

    return $products;
}

// Renvoie les premiers produits comme selection mise en avant.
function fetch_featured_products(int $limit = 3): array
{
    return array_slice(fetch_all_products(), 0, $limit);
}

// Recupere plusieurs produits a partir d'une liste d'identifiants.
function fetch_products_by_ids(array $ids): array
{
    $expectedIds = array_values(array_unique(array_map('intval', $ids)));

    if ($expectedIds === []) {
        return [];
    }

    $productsById = [];

    foreach (fetch_all_products() as $product) {
        if (in_array($product['id'], $expectedIds, true)) {
            $productsById[$product['id']] = $product;
        }
    }

    return $productsById;
}

// Recupere un seul produit par son identifiant.
function fetch_product_by_id(int $id): ?array
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare(
        'SELECT id, nom, description_courte, description_longue, prix_ht, stock, disponible, priorite, date_enregistrement
         FROM produit
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product || !is_candle_product($product)) {
        return null;
    }

    return enrich_product($product);
}

// Transforme le panier brut stocke en session en lignes completes,
// avec les donnees du produit et le total par ligne.
function fetch_cart_lines(): array
{
    $cart = get_cart();
    $productsById = fetch_products_by_ids(array_keys($cart));
    $lines = [];

    foreach ($cart as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;

        if ($quantity < 1 || !isset($productsById[$productId])) {
            continue;
        }

        $product = $productsById[$productId];
        $quantity = min($quantity, max(0, (int) $product['stock']));

        if ($quantity < 1) {
            continue;
        }

        $lines[] = [
            'product' => $product,
            'quantity' => $quantity,
            'line_total' => $product['prix_ht'] * $quantity,
        ];
    }

    return $lines;
}

// Calcule le total HT du panier.
function cart_total_price(): float
{
    $total = 0.0;

    foreach (fetch_cart_lines() as $line) {
        $total += (float) $line['line_total'];
    }

    return $total;
}

// Calcule le total TTC du panier.
function cart_total_price_tvac(): float
{
    return calculate_total_tvac(cart_total_price());
}

// Verifie si le panier peut encore etre valide.
// On signale les produits manquants, hors stock ou les quantites invalides.
function cart_validation_issues(): array
{
    $cart = get_cart();

    if ($cart === []) {
        return ['Ton panier est vide.'];
    }

    $productsById = fetch_products_by_ids(array_keys($cart));
    $issues = [];

    foreach ($cart as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;

        if ($quantity < 1) {
            $issues[] = 'Une quantite du panier est invalide.';
            continue;
        }

        if (!isset($productsById[$productId])) {
            $issues[] = 'Une bougie du panier n\'est plus disponible.';
            continue;
        }

        $product = $productsById[$productId];

        if (!$product['in_stock']) {
            $issues[] = 'La bougie "' . $product['nom'] . '" n\'est plus disponible.';
            continue;
        }

        if ($quantity > (int) $product['stock']) {
            $issues[] = 'La quantite demandee pour "' . $product['nom'] . '" depasse le stock disponible.';
        }
    }

    return array_values(array_unique($issues));
}

// Construit la reponse JSON envoyee au JavaScript apres modification du panier.
function build_cart_update_payload(array $product, int $quantity, bool $removed = false, string $message = ''): array
{
    $cartCount = cart_items_count();
    $cartTotal = cart_total_price();
    $cartTotalTvac = cart_total_price_tvac();
    $lineTotal = $quantity > 0 ? (float) $product['prix_ht'] * $quantity : 0.0;

    return [
        'quantity' => $quantity,
        'removed' => $removed,
        'empty' => $cartCount === 0,
        'cart_count' => $cartCount,
        'cart_count_label' => format_cart_count_label($cartCount),
        'cart_total' => $cartTotal,
        'cart_total_formatted' => format_price($cartTotal),
        'cart_total_tvac' => $cartTotalTvac,
        'cart_total_tvac_formatted' => format_price($cartTotalTvac),
        'line_total' => $lineTotal,
        'line_total_formatted' => format_price($lineTotal),
        'stock' => (int) $product['stock'],
        'message' => $message,
    ];
}

// Cree une commande a partir du panier actuel.
// Ici on utilise une transaction SQL pour que tout soit enregistre
// correctement ou rien du tout en cas de probleme.
function create_order_from_cart(array $deliveryData): int
{
    $issues = cart_validation_issues();

    if ($issues !== []) {
        throw new RuntimeException($issues[0]);
    }

    $cart = get_cart();
    $pdo = get_pdo();
    $selectProduct = $pdo->prepare(
        'SELECT id, nom, description_courte, description_longue, prix_ht, stock, disponible, priorite, date_enregistrement
         FROM produit
         WHERE id = :id
         LIMIT 1
         FOR UPDATE'
    );
    $insertOrder = $pdo->prepare(
        'INSERT INTO commande (total_ht, total_tvac, adresse_livraison, email)
         VALUES (:total_ht, :total_tvac, :adresse_livraison, :email)'
    );
    $insertOrderLine = $pdo->prepare(
        'INSERT INTO commande_produit (commande_id, produit_id, quantite)
         VALUES (:commande_id, :produit_id, :quantite)'
    );
    $updateStock = $pdo->prepare(
        'UPDATE produit
         SET stock = :stock, disponible = :disponible
         WHERE id = :id'
    );

    try {
        // Debut de la transaction : on verrouille et on met a jour
        // toutes les donnees de commande comme un seul bloc.
        $pdo->beginTransaction();

        $lineItems = [];
        $totalHt = 0.0;

        foreach ($cart as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity = (int) $quantity;

            if ($quantity < 1) {
                continue;
            }

            $selectProduct->execute(['id' => $productId]);
            $product = $selectProduct->fetch(PDO::FETCH_ASSOC);

            if (!$product || !is_candle_product($product)) {
                throw new RuntimeException('Une bougie du panier n\'est plus disponible.');
            }

            $stock = (int) $product['stock'];
            $isAvailable = (int) $product['disponible'] === 1 && $stock > 0;

            if (!$isAvailable || $quantity > $stock) {
                throw new RuntimeException('Le stock de "' . $product['nom'] . '" a change. Retourne au panier pour le verifier.');
            }

            $lineTotal = (float) $product['prix_ht'] * $quantity;
            $totalHt += $lineTotal;

            // On memorise les donnees utiles pour les requetes suivantes.
            $lineItems[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'next_stock' => $stock - $quantity,
            ];
        }

        if ($lineItems === []) {
            throw new RuntimeException('Ton panier est vide.');
        }

        $totalTvac = calculate_total_tvac($totalHt);
        $insertOrder->execute([
            'total_ht' => round($totalHt, 2),
            'total_tvac' => $totalTvac,
            'adresse_livraison' => build_delivery_address_text($deliveryData),
            'email' => sanitize_delivery_form_input($deliveryData)['email'],
        ]);

        $orderId = (int) $pdo->lastInsertId();

        foreach ($lineItems as $lineItem) {
            // 1) On ajoute la ligne de commande
            $insertOrderLine->execute([
                'commande_id' => $orderId,
                'produit_id' => $lineItem['product_id'],
                'quantite' => $lineItem['quantity'],
            ]);

            // 2) On met a jour le stock du produit
            $updateStock->execute([
                'stock' => $lineItem['next_stock'],
                'disponible' => $lineItem['next_stock'] > 0 ? 1 : 0,
                'id' => $lineItem['product_id'],
            ]);
        }

        // Si tout s'est bien passe, on valide les changements.
        $pdo->commit();

        // Une fois la commande enregistree, on vide le panier et le formulaire.
        $_SESSION['panier'] = [];
        clear_delivery_form_data();

        return $orderId;
    } catch (Throwable $exception) {
        // En cas d'erreur, on annule tous les changements faits dans la transaction.
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

// Recupere les lignes d'une commande deja enregistree.
function fetch_order_items(int $orderId): array
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare(
        'SELECT cp.quantite, p.nom, p.prix_ht
         FROM commande_produit cp
         LEFT JOIN produit p ON p.id = cp.produit_id
         WHERE cp.commande_id = :commande_id
         ORDER BY p.nom ASC'
    );
    $stmt->execute(['commande_id' => $orderId]);

    $items = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $quantity = (int) ($row['quantite'] ?? 0);
        $unitPrice = (float) ($row['prix_ht'] ?? 0);
        $items[] = [
            'name' => (string) ($row['nom'] ?? 'Bougie'),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice * $quantity,
        ];
    }

    return $items;
}

// Recupere le resume complet d'une commande.
function fetch_order_summary(int $orderId): ?array
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare(
        'SELECT c.id, c.date_creation, c.total_ht, c.total_tvac, c.adresse_livraison, c.email,
                COALESCE(SUM(cp.quantite), 0) AS items_count
         FROM commande c
         LEFT JOIN commande_produit cp ON cp.commande_id = c.id
         WHERE c.id = :id
         GROUP BY c.id, c.date_creation, c.total_ht, c.total_tvac, c.adresse_livraison, c.email
         LIMIT 1'
    );
    $stmt->execute(['id' => $orderId]);

    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$summary) {
        return null;
    }

    $summary['id'] = (int) $summary['id'];
    $summary['items_count'] = (int) $summary['items_count'];
    $summary['total_ht'] = (float) $summary['total_ht'];
    $summary['total_tvac'] = (float) $summary['total_tvac'];
    $summary['items'] = fetch_order_items($orderId);

    return $summary;
}

// -----------------------------------------------------------------------------
// CATALOGUE ET FILTRES
// -----------------------------------------------------------------------------
// Liste des categories visibles dans le formulaire de filtres.
function catalog_categories(): array
{
    return [
        'all' => 'Toutes',
        'boisee' => 'Boisee',
        'gourmande' => 'Gourmande',
        'florale' => 'Florale',
        'fraiche' => 'Fraiche',
        'signature' => 'Signature',
    ];
}

// Calcule le prix maximum disponible dans le catalogue.
function catalog_max_price(array $products): float
{
    $maxPrice = 0.0;

    foreach ($products as $product) {
        if ($product['prix_ht'] > $maxPrice) {
            $maxPrice = (float) $product['prix_ht'];
        }
    }

    return $maxPrice > 0 ? ceil($maxPrice) : 50.0;
}

// Verifie si la categorie demandee existe bien.
function sanitize_catalog_category(string $category): string
{
    return array_key_exists($category, catalog_categories()) ? $category : 'all';
}

// Verifie si le tri demande est autorise.
function sanitize_catalog_sort(string $sort): string
{
    $allowedSorts = ['popular', 'priceAsc', 'priceDesc', 'nameAsc'];

    return in_array($sort, $allowedSorts, true) ? $sort : 'popular';
}

// Lit les filtres de l'URL et leur donne des valeurs propres et previsibles.
function get_catalog_filters(): array
{
    $products = fetch_all_products();
    $maxAvailable = catalog_max_price($products);
    $category = sanitize_catalog_category((string) ($_GET['category'] ?? 'all'));
    $sort = sanitize_catalog_sort((string) ($_GET['sort'] ?? 'popular'));

    $maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : $maxAvailable;
    if ($maxPrice <= 0) {
        $maxPrice = $maxAvailable;
    }

    return [
        'q' => trim((string) ($_GET['q'] ?? '')),
        'category' => $category,
        'in_stock' => isset($_GET['in_stock']) && $_GET['in_stock'] === '1',
        'max_price' => $maxPrice,
        'max_available' => $maxAvailable,
        'sort' => $sort,
    ];
}

// Verifie si un produit respecte tous les filtres du catalogue.
function product_matches_catalog_filters(array $product, array $filters): bool
{
    if ($filters['q'] !== '') {
        $needle = normalize_text($filters['q']);

        if (!str_contains(product_search_text($product), $needle)) {
            return false;
        }
    }

    if ($filters['category'] !== 'all' && $product['category_slug'] !== $filters['category']) {
        return false;
    }

    if ($filters['in_stock'] && !$product['in_stock']) {
        return false;
    }

    if ($product['prix_ht'] > $filters['max_price']) {
        return false;
    }

    return true;
}

// Compare deux produits pour savoir dans quel ordre les afficher.
function compare_catalog_products(array $left, array $right, string $sort): int
{
    return match ($sort) {
        'priceAsc' => $left['prix_ht'] <=> $right['prix_ht'],
        'priceDesc' => $right['prix_ht'] <=> $left['prix_ht'],
        'nameAsc' => strcmp($left['nom'], $right['nom']),
        default => [$left['priorite'], $left['nom']] <=> [$right['priorite'], $right['nom']],
    };
}

// Filtre puis trie les produits pour preparer l'affichage du catalogue.
function filter_products(array $products, array $filters): array
{
    $filtered = array_values(array_filter(
        $products,
        static fn (array $product): bool => product_matches_catalog_filters($product, $filters)
    ));

    usort(
        $filtered,
        static fn (array $left, array $right): int => compare_catalog_products($left, $right, $filters['sort'])
    );

    return $filtered;
}

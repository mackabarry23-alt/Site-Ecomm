<?php
declare(strict_types=1);

// Ce fichier est l'entree la plus simple du projet.
// Il redirige vers la vraie page d'accueil situee dans /page.
require_once __DIR__ . '/page/includes/site.php';

header('Location: ' . app_url('page/index.php'), true, 302);
exit;

<?php
declare(strict_types=1);

require_once __DIR__ . '/page/includes/site.php';

header('Location: ' . app_url('page/index.php'), true, 302);
exit;

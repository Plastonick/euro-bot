<?php

require_once __DIR__ . '/vendor/autoload.php';

set_time_limit(0);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->safeLoad();

if ($_ENV['DB_HOST'] ?? null) {
    // set the API running if we have database credentials
    $indexPath = __DIR__ . '/public/index.php';

    exec("nohup php -S 0.0.0.0:80 {$indexPath} > /dev/null 2>&1 &");
}

// set the main logic loop
include __DIR__ . '/src/App.php';

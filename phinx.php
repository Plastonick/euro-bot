<?php

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'name' => 'sweepstake',
            'user' => $_ENV['DB_USER'] ?? 'sweepstake',
            'pass' => $_ENV['DB_PASS'] ?? 'sweepstake',
            'port' => $_ENV['DB_PORT'] ?? '5444',
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'execution',
];

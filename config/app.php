<?php

/**
 * Загружаем из файла .env переменные среды, хранящие секретные параметры конфигурации
 */
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(dirname(__DIR__));
$dotenv->load();

$db = require_once __DIR__ . '/db.php';

return (object)[
    'db' => $db,
    'data' => (object)[
        'secretKey' => getenv('APP_SECRET_KEY'),
        'domain' => getenv('APP_DOMAIN'),
        'aud' => [
            'html' => getenv('AUD_HTML')
        ]
    ]
];

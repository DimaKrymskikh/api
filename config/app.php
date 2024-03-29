<?php

/**
 * Загружаем из файла .env переменные среды, хранящие секретные параметры конфигурации
 */
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(dirname(__DIR__));
$dotenv->load();

$db = require_once __DIR__ . '/db.php';

// Параметры приложения
return (object)[
    'app_url' => dirname(__DIR__) . '/app',

    'db' => $db,
    'data' => (object)[
        'secretKey' => getenv('APP_SECRET_KEY'),
        'domain' => getenv('APP_DOMAIN'),
        'aud' => [
            'html' => getenv('AUD_HTML'),
            'vue' => getenv('AUD_VUE')
        ]
    ]
];

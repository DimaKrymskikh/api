<?php

/**
 * Настройки базы данных
 */
$dsn = getenv('DB_CONNECTION') . ':host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE');

return (object) [
    'dsn' => $dsn,
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => 'utf8',
];

<?php
/**
 * Точка входа приложения
 */

use App\App;

// Получаем пространство имён вендоров
require_once __DIR__ . '/../vendor/autoload.php';
// Получаем параметры конфигурации
$config = require_once __DIR__ . '/../config/app.php';
// Запускаем приложение
(new App($config))->run();

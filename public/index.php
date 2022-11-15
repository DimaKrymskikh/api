<?php

use App\App;

require_once __DIR__ . '/../vendor/autoload.php';

$config = require_once __DIR__ . '/../config/app.php';

(new App($config))->run();


<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/librarya_System/vendor/autoload.php';
$app = require_once __DIR__.'/librarya_System/bootstrap/app.php';

$app->handleRequest(Request::capture());



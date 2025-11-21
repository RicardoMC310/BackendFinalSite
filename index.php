<?php

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/src/Routes/main.php";

use App\Core\Router;

$path = $_GET["path"] ?? "/";

$path = "/" . trim($path, "/");

Router::dispatch($path, $_SERVER["REQUEST_METHOD"]);
<?php

require_once 'src/services/SessionManager.php';
require_once 'src/services/ErrorHandler.php';

SessionManager::start();
ErrorHandler::register();

require_once 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

Routing::run($path);
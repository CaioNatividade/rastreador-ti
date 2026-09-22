<?php

// Somente servidor de testes local; não enviar ao InfinityFree.
declare(strict_types=1);

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_GET['rota'] = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
require dirname(__DIR__) . '/index.php';

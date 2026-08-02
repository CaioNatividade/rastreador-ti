<?php

declare(strict_types=1);

use Core\Router;

require_once dirname(__DIR__) . '/vendor/autoload.php';

header('Content-Type: text/html; charset=UTF-8');

try {
    (new Router())->run();
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);

    echo '<h1>Erro interno</h1>';
    echo '<p>Não foi possível processar a solicitação.</p>';
}

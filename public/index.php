<?php

declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

use Core\Router;

require_once dirname(__DIR__) . '/vendor/autoload.php';

header('Content-Type: text/html; charset=UTF-8');

// Timeout de sessão: 1800 segundos = 30 minutos de inatividade
if (isset($_SESSION['usuario_id'], $_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso']) > 1800) {
    $_SESSION = [];
    session_destroy();

    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    header('Location: ' . $basePath . '/login?sessao=expirada');
    exit;
}

$_SESSION['ultimo_acesso'] = time();

try {
    (new Router())->run();
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);

    echo '<h1>Erro interno</h1>';
    echo '<p>Não foi possível processar a solicitação.</p>';
}

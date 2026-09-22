<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
date_default_timezone_set('America/Sao_Paulo');

/** Sempre usa um banco novo em loopback. Nunca carrega database.local.php. */
function testDatabase(): array
{
    $host = '127.0.0.1';
    $port = getenv('TEST_DB_PORT') ?: '13316';
    $user = getenv('TEST_DB_USER') ?: 'root';
    $password = getenv('TEST_DB_PASS');
    if ($password === false) {
        throw new RuntimeException('Defina TEST_DB_PASS para o MariaDB local de testes.');
    }
    $db = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $name = 'rastreio_test_' . bin2hex(random_bytes(6));
    $db->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db->exec("USE `$name`");
    $db->exec(file_get_contents(dirname(__DIR__) . '/database/rastreio_ti.sql'));
    $property = new ReflectionProperty(Config\Database::class, 'instance');
    $property->setAccessible(true);
    $property->setValue(null, $db);
    return [$db, $name, ['DB_HOST' => $host, 'DB_PORT' => $port, 'DB_USER' => $user, 'DB_PASS' => $password, 'DB_NAME' => $name, 'DB_CHARSET' => 'utf8mb4']];
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FALHOU: ' . $message);
    }
    echo 'OK: ' . $message . PHP_EOL;
}

function rejected(callable $action, string $message): void
{
    try {
        $action();
    } catch (DomainException | PDOException) {
        check(true, $message);
        return;
    }
    throw new RuntimeException('FALHOU: deveria rejeitar ' . $message);
}

function dropTestDatabase(PDO $db, string $name): void
{
    if (!preg_match('/^rastreio_test_[a-f0-9]{12}$/D', $name)) {
        throw new RuntimeException('Nome de banco de teste inválido.');
    }
    $db->exec("DROP DATABASE `$name`");
}

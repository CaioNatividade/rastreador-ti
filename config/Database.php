<?php

declare(strict_types=1);

namespace Config;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = self::configuration();
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            self::$instance = new PDO(
                $dsn,
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$instance;
    }

    /** @return array{host: string, port: int, dbname: string, user: string, pass: string, charset: string} */
    private static function configuration(): array
    {
        $localFile = __DIR__ . '/database.local.php';
        $local = is_file($localFile) ? require $localFile : [];

        if (!is_array($local)) {
            $local = [];
        }

        return [
            'host' => self::environment('DB_HOST', $local['host'] ?? 'localhost'),
            'port' => (int) self::environment('DB_PORT', (string) ($local['port'] ?? 3306)),
            'dbname' => self::environment('DB_NAME', $local['dbname'] ?? 'rastreio_ti'),
            'user' => self::environment('DB_USER', $local['user'] ?? 'root'),
            'pass' => self::environment('DB_PASS', $local['pass'] ?? ''),
            'charset' => self::environment('DB_CHARSET', $local['charset'] ?? 'utf8mb4'),
        ];
    }

    private static function environment(string $name, string $fallback): string
    {
        $value = getenv($name);

        return $value === false ? $fallback : $value;
    }
}

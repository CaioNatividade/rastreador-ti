<?php

declare(strict_types=1);

namespace Config;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    private const HOST = 'localhost';
    private const DBNAME = 'rastreio_ti';
    private const USER = 'root';
    private const PASSWORD = '';

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                self::HOST,
                self::DBNAME
            );

            self::$instance = new PDO(
                $dsn,
                self::USER,
                self::PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$instance;
    }
}

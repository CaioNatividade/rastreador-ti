<?php

class Database
{
    // Guarda a única instância da conexão PDO durante a execução da página
    private static ?PDO $instancia = null;

    // Dados de acesso ao banco (ajustar host/usuário/senha se necessário)
    private const HOST = 'localhost';
    private const DBNAME = 'rastreio_ti';
    private const USER = 'root';
    private const SENHA = '';

    public static function getInstance(): PDO
    {
        // Se ainda não existe conexão aberta, cria uma agora
        if (self::$instancia === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DBNAME . ';charset=utf8mb4';

            try {
                self::$instancia = new PDO($dsn, self::USER, self::SENHA);

                // Faz o PDO lançar exceções em caso de erro de SQL,
                self::$instancia->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
                );

                // Faz o PDO retornar os dados como array associativo por padrão
                self::$instancia->setAttribute(
                    PDO::ATTR_DEFAULT_FETCH_MODE,
                    PDO::FETCH_ASSOC
                );


            } catch (PDOException $erro) {
                // Em produção isso deveria ir para um log, não para a tela.
                // Por enquanto, nível básico: apenas interrompe e avisa.
                die('Falha ao conectar ao banco de dados: ' . $erro->getMessage());
            }
        }

        return self::$instancia;
    }
}

<?php

/**
 * Database
 * --------
 * Responsável por abrir (e reaproveitar) a conexão com o banco
 * de dados MySQL usando PDO.
 *
 * Uso em qualquer Model:
 *   $pdo = Database::getInstance();
 *
 * Por que "getInstance" e não criar um "new Database()" toda hora?
 * Porque abrir uma conexão nova a cada consulta é caro. Guardamos
 * a conexão já aberta numa propriedade estática e só criamos de
 * novo se ainda não existir (padrão conhecido como Singleton).
 */
class Database
{
    // Guarda a única instância da conexão PDO durante a execução da página
    private static ?PDO $instancia = null;

    // Dados de acesso ao banco (ajustar host/usuário/senha se necessário)
    private const HOST   = 'localhost';
    private const DBNAME = 'rastreio_ti';
    private const USER   = 'root';
    private const SENHA  = '';

    public static function getInstance(): PDO
    {
        // Se ainda não existe conexão aberta, cria uma agora
        if (self::$instancia === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DBNAME . ';charset=utf8mb4';

            try {
                self::$instancia = new PDO($dsn, self::USER, self::SENHA);

                // Faz o PDO lançar exceções em caso de erro de SQL,
                // em vez de falhar silenciosamente
                self::$instancia->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
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

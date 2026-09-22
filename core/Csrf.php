<?php

declare(strict_types=1);

namespace Core;

class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function validar(): void
    {
        $enviado = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';
        $esperado = (string) ($_SESSION[self::SESSION_KEY] ?? '');

        if ($esperado === '' || !hash_equals($esperado, $enviado)) {
            http_response_code(403);
            echo '<h1>Solicitação não autorizada</h1>';
            echo '<p>Sessão expirada ou token de segurança inválido. Recarregue o formulário e tente novamente.</p>';
            exit;
        }
    }
}

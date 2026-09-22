<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

abstract class Controller
{
    protected function field(string $name, bool $trim = true): string
    {
        $value = $_POST[$name] ?? '';
        return is_string($value) ? ($trim ? trim($value) : $value) : '';
    }

    protected function postedId(string $name = 'id'): int
    {
        $value = filter_var($this->field($name), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($value === false) {
            throw new \DomainException('Identificador inválido.');
        }
        return $value;
    }

    protected function flash(string $message, bool $error = false): void
    {
        $_SESSION['flash'] = ['message' => $message, 'error' => $error];
    }

    /** Executa POST protegido e preserva os campos não sensíveis em caso de erro. */
    protected function submit(callable $action, string $route): never
    {
        Csrf::validar();
        try {
            $action();
            $this->flash('Operação realizada com sucesso.');
            unset($_SESSION['form_old']);
        } catch (\DomainException $error) {
            $this->flash($error->getMessage(), true);
            $_SESSION['form_old'] = array_diff_key($_POST, array_flip(['csrf', 'senha', 'senha_atual', 'confirmacao']));
        } catch (\PDOException $error) {
            error_log($error->__toString());
            $this->flash('Não foi possível salvar. Verifique valores duplicados ou registros vinculados e tente novamente.', true);
            $_SESSION['form_old'] = array_diff_key($_POST, array_flip(['csrf', 'senha', 'senha_atual', 'confirmacao']));
        }
        $this->redirect($route);
    }

    /** @param array<string, mixed> $data */
    protected function view(string $viewName, array $data = []): void
    {
        $viewsDirectory = dirname(__DIR__) . '/app/Views';
        $view = $viewsDirectory . '/' . $viewName . '.php';

        if (!is_file($view)) {
            throw new RuntimeException('View não encontrada.');
        }

        extract($data, EXTR_SKIP);
        require $viewsDirectory . '/layouts/main.php';
    }

    /** @param array<string, mixed> $data */
    protected function viewAuth(string $viewName, array $data = []): void
    {
        $view = dirname(__DIR__) . '/app/Views/' . $viewName . '.php';

        if (!is_file($view)) {
            throw new RuntimeException('View não encontrada.');
        }

        extract($data, EXTR_SKIP);
        require $view;
    }

    protected function redirect(string $route): never
    {
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        header('Location: ' . $basePath . '/' . ltrim($route, '/'));
        exit;
    }
}

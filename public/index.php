<?php
require_once '../app/controllers/HomeController.php';

$rota = $_GET['rota'] ?? 'home';

switch ($rota) {
    case 'home':
        // $controller = new HomeController();
        HomeController::index();
        break;
    case 'equipamentos':
        HomeController::equipamentos();
        break;
    case 'categorias':
        HomeController::categorias();
        break;
    case 'emprestimos':
        HomeController::emprestimos();
        break;
    default:
        echo "Erro 404 - Página não encontrada";
        break;
}

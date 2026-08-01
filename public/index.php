<?php

use Core\Router;

require_once '../vendor/autoload.php';

$router = new Router();
$router->run();


// $rota = $_GET['rota'] ?? 'home';
// echo $rota;
// switch ($rota) {
//     case 'home':
//         // $controller = new HomeController();
//         HomeController::index();
//         break;
//     case 'equipamentos':
//         HomeController::equipamentos();
//         break;
//     case 'categorias':
//         HomeController::categorias();
//         break;
//     case 'emprestimos':
//         HomeController::emprestimos();
//         break;
//     default:
//         echo "Erro 404 - Página não encontrada";
//         break;
// }

<?php
require_once '../app/controllers/HomeController.php';

$rota = $_GET['rota'] ?? 'home';

switch ($rota) {
  case 'home':
    $controller = new HomeController();
    $controller->index();
    break;
  default:
    echo "Página não encontrada";
}

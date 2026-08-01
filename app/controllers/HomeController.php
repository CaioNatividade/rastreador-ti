<?php

namespace App\Controllers;

class HomeController {
  public static function index() {
    require '../app/views/main.php';
  }

  public static function equipamentos() {
    require '../app/views/equipamentos.php';
  }

  public static function categorias() {
    require '../app/views/categorias.php';
  }

  public static function emprestimos() {
    require '../app/views/emprestimos.php';
  }
}

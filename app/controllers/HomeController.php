<?php

namespace App\Controllers;

use App\Models\EquipamentosModel;
use Core\Controller;

class HomeController extends Controller {
  public function index() {
    $model = new EquipamentosModel();
    $dataEquipamentos = $model->getAllRows();
  
    $this->view("home", [
      'dataEquipamentos' => $dataEquipamentos
    ]);
  }

  public function equipamentos() {
    $this->view("equipamentos");
  }

  public function categorias() {
    $this->view("categorias");
  }

  public function emprestimos() {
    $model = new EquipamentosModel();
    $dataEquipamentos = $model->getAllRows();
  
    $this->view("emprestimos", [
      'dataEquipamentos' => $dataEquipamentos
    ]);
  }

  public function manutencoes() {
    $this->view("manutencoes");
  }

  public function usuarios() {
    $this->view("usuarios");
  }
}

<?php

namespace App\Models;

class EquipamentosModel {
    public int $id, $categoria_id, $status;
    public string $nome, $marca, $modelo, $numero_serie, $data_aquisicao, $observacoes, $criado_em, $atualizado_em;
    public $rows;

    public function save(){
        $con = new EquipamentosRepository();
        if ($this->id){
            $con->update($this);
        } else{
            $con->insert($this);
        }
    }

    public function getAllRows(){
        $con = new EquipamentosRepository();
        return $this->rows = $con->select();
        
    }
    
}

?>
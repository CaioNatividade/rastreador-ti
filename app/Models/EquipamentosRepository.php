<?php
namespace App\Models;
use Config\Database;
use PDO;

class EquipamentosRepository{
    private $con;

    public function __construct(){
        $this->con = Database::getInstance();
    }

    public function insert(EquipamentosModel $model){

    }

    public function update($model){

    }

    public function select(){
        $sql = "SELECT * FROM equipamentos";
        $stmt = $this->con->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
<?php

namespace Core;

class Controller{
    public function view($view, $data = []){
        extract($data);
        $view = __DIR__ . "/../app/Views/" . $view . ".php";
        include __DIR__ . "/../app/Views/layouts/main.php";
    }
}


?>
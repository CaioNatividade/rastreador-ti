<?php

namespace Core;

class Router{
    public function run(){
        $url = $_GET['rota'] ?? 'home';
        $url = explode("/", $url);

        $controllerName = "App\\Controllers\\" . ucfirst($url[0]) . "Controller";
        $methodName = $url[1] ?? "index";

        if (class_exists($controllerName)){
            $controller = new $controllerName();

            if(method_exists($controller, $methodName)){
                $controller->$methodName();
            }else {
                echo "Método " . $methodName . " não encontrado";
            }
        } else {
            echo "Controller " . $controllerName . " não encontrado";
        }
    }
}

?>
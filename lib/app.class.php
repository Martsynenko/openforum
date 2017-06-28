<?php

class App{

    protected static $router;

    public static $db;

    public static function getRouter(){
        return self::$router;
    }

    public static function run($uri){

        self::$router = new Router($uri);

        self::$db = new DB(Config::get('db_host'), Config::get('db_user'), Config::get('db_password'), Config::get('db_name'));

        $controller_class = ucfirst(self::$router->getController()).'Controller';
        $controller_method = strtolower(self::$router->getMethodPrefix().self::$router->getAction());

        $controller_object = new $controller_class();

        if(method_exists($controller_object, $controller_method)){
            $view_path = $controller_object->$controller_method();
            $view_object = new View($controller_object->getData(), $view_path);
            $content = $view_object->render();
        } else {
            throw new Exception('Данный метод не существует');
        }

        if(isset($_SESSION['auth_user'])){
            $layout_path = VIEW_PATH.DS.'user.html';
        } elseif(isset($_SESSION['admin_user'])){
            $layout_path = VIEW_PATH.DS.'admin.html';
        } elseif(isset($_SESSION['rand_user'])){
            $layout_path = VIEW_PATH.DS.'default.html';
        }

        $layout_view_object = new View(compact(['content']), $layout_path);
        echo $layout_view_object->render();
    }
}
<?php

class View{

    protected $data;
    protected $path;

    public function __construct($data = [], $path = null)
    {
        if(!$path){
            $path = self::getDefueltViewPath();
        }

        if(!file_exists($path)){
            throw new Exception('Данный шаблона не существует');
        }

        $this->data = $data;
        $this->path = $path;
    }

    public static function getDefueltViewPath(){
        $router = App::getRouter();

        if(!$router){
            return false;
        }

        $controller_dir = $router->getController();
        $template_name = $router->getMethodPrefix().$router->getAction().'.html';

        return VIEW_PATH.DS.$controller_dir.DS.$template_name;
    }

    public function render(){
        $data = $this->data;

        ob_start();
        include $this->path;
        $content = ob_get_clean();

        return $content;
    }
}
<?php

date_default_timezone_set('Europe/Kiev');

    session_start();

    define('DS', DIRECTORY_SEPARATOR);
    define('ROOT', dirname(dirname(__FILE__)));
    define('VIEW_PATH', ROOT.DS.'views');
    define('MAIL_PATH', ROOT.DS.'views'.DS.'admin_mail');

    require_once (ROOT.DS.'lib'.DS.'init.php');

    App::run($_SERVER['REQUEST_URI']);

//$router = new Router($_SERVER['REQUEST_URI']);
//echo "<pre>";
//print_r('Маршрут: '.$router->getRoute().'<br>');
//print_r('Контроллер: '.$router->getController().'<br>');
//print_r('Метод: '.$router->getMethodPrefix().$router->getAction().'<br>');
//echo 'Параметры: <br>';
//print_r($router->getParams());

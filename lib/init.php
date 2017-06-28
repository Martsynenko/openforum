<?php

spl_autoload_register(function($classname){
    $lib_path = ROOT.DS.'lib'.DS.strtolower($classname).'.class.php';
    $controllers_path = ROOT.DS.'controllers'.DS.strtolower(str_replace('Controller', '', $classname)).'.controller.php';
    $model_path = ROOT.DS.'models'.DS. $classname .'.php';

    if(file_exists($lib_path)){
        require_once $lib_path;
    } elseif (file_exists($controllers_path)){
        require_once $controllers_path;
    } elseif (file_exists($model_path)){
        require_once $model_path;
    } else {
        throw new Exception('Файл не найден по даному классу');
    }
});


require_once(ROOT . DS . 'config' . DS . 'config.php');
require_once(ROOT . DS . 'lib' . DS . 'lib_wide'. DS . 'WideImage.php');
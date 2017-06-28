<?php

class Session {

    protected static $flash_message = [];

    public static function setFlash($key, $message){
        self::$flash_message[$key] = $message;
    }

    public static function hasFlash($key){
        if(isset(self::$flash_message[$key])){
            return self::$flash_message[$key];
        }
        return null;
    }

    public static function flash($key){
        echo self::$flash_message[$key];
        self::$flash_message = null;
    }

    public static function set($key, $value){
        $_SESSION[$key] = $value;
    }

    public static function get($key){
        if(isset($_SESSION[$key])){
            return $_SESSION[$key];
        }
        return null;
    }

    public static function delete($key){
        if(isset($_SESSION[$key])){
            unset($_SESSION[$key]);
        }
    }

    public static function destroy(){
        session_destroy();
    }
}
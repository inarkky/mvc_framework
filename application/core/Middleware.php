<?php


namespace application\core;


use application\middleware\Logger;
use application\middleware\Persister;

class Middleware
{

    public static function debug()
    {
        Logger::register();
    }

    public static function persister()
    {
        Persister::run();
    }

}
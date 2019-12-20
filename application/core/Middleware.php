<?php

namespace application\core;


use application\middleware\Logger;
use application\middleware\Persister;

class Middleware
{

    public static function debug()
    {
        Logger::run();
    }

    public static function persist()
    {
        Persister::run();
    }

}
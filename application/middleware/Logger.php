<?php

namespace application\middleware;


use application\helpers\Debug;

class Logger extends Debug
{
    public static function run()
    {
        self::register();
    }
}
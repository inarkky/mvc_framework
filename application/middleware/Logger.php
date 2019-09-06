<?php

namespace application\middleware;


use application\helpers\Debug;

class Logger extends Debug
{
    public function __construct()
    {
        Debug::register();

            if (!function_exists('ldump')) {
                function ldump()
                {
                    $args = func_get_args();
                    Debug::log(...$args);
                }
            }
            if (!function_exists('ddump')) {
                function ddump()
                {
                    $args = func_get_args();
                    Debug::dump(...$args);
                }
            }
            if (!function_exists('cdump')) {
                function cdump()
                {
                    $args = func_get_args();
                    Debug::chrono(...$args);
                }
            }
    }
}
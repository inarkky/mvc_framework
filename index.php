<?php

use application\core\Router;
use application\core\Middleware;
use application\helpers\Debug;

define('ROOT_PATH', __DIR__. '/');
require_once ROOT_PATH . 'application/config/config.php';

spl_autoload_register(function($class) {
    $path = str_replace('\\', '/', $class.'.php');
    if (file_exists($path)) {
        require $path;
    }
});

if(ENVIRONMENT === 'dev') {
    Middleware::debug();
}elseif(ENVIRONMENT === 'prod'){
    error_reporting(0);
    ini_set('display_errors', 0);
}else{
    die('Fatal error: environment not defined or valid!');
}

Middleware::persist();

$router = new Router;
$router->run();

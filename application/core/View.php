<?php

namespace application\core;


use application\helpers\CSRF;

class View 
{

	public $path;
	public $route;
	public $layout = 'default';

	public function __construct($route) 
	{
		$this->route = $route;
		$this->path = $route['controller'].'/'.$route['action'];
	}

	public function render($title, $vars = []) 
	{
	    $csrf = new CSRF();
		extract($vars);
		$path = PUBLIC_PATH . $this->path. '.php';
		if (file_exists($path)) {
			ob_start();
			require $path;
			$content = ob_get_clean();
			require PUBLIC_PATH . '/layouts/'.$this->layout.'.php';
		}
	}

	public static function errorCode($code) 
	{
		http_response_code($code);
		$path = PUBLIC_PATH . '/errors/'.$code.'.php';
		if (file_exists($path)) {
			require $path;
		}
		exit;
	}

    public static function easterEgg()
    {
        $path = PUBLIC_PATH . '/easteregg/index.php';
        if (file_exists($path)) {
            require $path;
        }
        exit;
    }

    public function redirect($url)
    {
        header('location: '.$url);
        exit;
    }


    public function message($status, $message)
	{
		exit(json_encode(['status' => $status, 'message' => $message]));
	}

	public function location($url) 
	{
		exit(json_encode(['url' => $url]));
	}

}	
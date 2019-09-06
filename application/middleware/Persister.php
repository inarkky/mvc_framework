<?php

namespace application\middleware;


use application\helpers\SecureSession;
use application\helpers\Cookies;

class Persister
{
    public static $session;
    public static $cookies;

    public static function run()
    {
        $session = new SecureSession(SESSION_KEY, 'PHPSESSID');

        $cookies = new Cookies('SESSION_COOKIE');
        $cookies->expires = 0;
        $cookies->value = 'TRUE';
        $cookies->set();

        ini_set('session.save_handler', 'files');
        session_set_save_handler($session, true);
        session_save_path(SESSION_PATH);

        $session->start();
    }
}
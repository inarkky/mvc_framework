<?php

namespace application\helpers;


class CSRF
{
    public static $name = '_CSRF';

    public function __construct()
    {
        $this->session = new SecureSession();
        $this->session->start();
    }

    public function insert($form = 'default')
    {
        echo '<input type="hidden" name="csrf_token" value="' . $this->generate($form) . '">';
    }


    public function generate($form = NULL)
    {
        $token = self::token() . self::fingerprint();
        $this->session->put(self::$name . '_' . $form,  $token);
        return $token;
    }

    public function check($token, $form = NULL)
    {
        if ($this->session->get(self::$name . '_' . $form) && $this->session->get(self::$name . '_' . $form) === $token) { // token OK
            return (substr($token, -32) === self::fingerprint()); // fingerprint OK?
        }
        return FALSE;
    }

    protected static function token()
    {
        mt_srand((double) microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), TRUE)));
        return substr($charid, 0, 8) .
               substr($charid, 8, 4) .
               substr($charid, 12, 4) .
               substr($charid, 16, 4) .
               substr($charid, 20, 12);
    }

    protected static function fingerprint()
    {
        return strtoupper(md5(implode('|', array($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']))));
    }
}
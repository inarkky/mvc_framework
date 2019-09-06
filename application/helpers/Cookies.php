<?php

namespace application\helpers;


class Cookies
{

    public $name;
    public $value = '';
    public $expires;
    public $path = '/';
    public $domain = '';
    public $secure = false;
    public $http_only = false;


    public function __construct($name, $value = null)
    {
        $this->name = $name;
        $this->domain = '.' . $_SERVER['SERVER_NAME'];

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] !== 'off') {
            $this->secure = true;
        }

        if ($value !== null) {
            $this->value = $value;
        }
    }

    public function expires_in($time, $unit = "months")
    {
        if (!empty($time)) {
            switch ($unit) {
                case 'months' :
                    $time = $time * 60 * 60 * 24 * 31;
                    break;
                case 'days'   :
                    $time = $time * 60 * 60 * 24;
                    break;
                case 'hours'  :
                    $time = $time * 60 * 60;
                    break;
                case 'minutes':
                    $time *= 60;
                    break;
            }
        }
        $this->expires_at($time);
    }

    public function expires_at($time)
    {
        if (empty($time)) {
            $time = null;
        }
        $this->expires = $time;
    }

    public function set()
    {
        return setcookie(
            $this->name,
            $this->value,
            $this->expires,
            $this->path,
            $this->domain,
            $this->secure,
            $this->http_only
        );
    }

    public function get()
    {
        return ($this->value === '' && isset($_COOKIE[$this->name])) ? $_COOKIE[$this->name] : $this->value;
    }

    public function delete()
    {
        $this->value = '';
        $this->expires = time() - 3600;
        return $this->set();
    }
}
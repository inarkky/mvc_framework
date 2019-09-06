<?php

namespace application\helpers;


use SessionHandler;

class SecureSession extends SessionHandler
{

    protected $key, $name, $cookie;

    public function __construct($key = '', $name = 'SESSION')
    {
        $this->key = $key;
        $this->name = $name;
        if(!isset($_SESSION)) {
            $this->setup();
        }
    }

    private function _encrypt($data, $key) {
        $encryption_key = base64_decode($key);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        if($iv===false) die('Fatal error: encryption was not successful - could not save data!!'); // weak encryption

        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    private function _decrypt($data, $key) {
        $encryption_key = base64_decode($key);
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }

    protected function setup()
    {
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);

        session_name($this->name);
    }

    public function start()
    {
        if (session_id() === '') {
            session_start();
            return (mt_rand(0, 4) === 0) ? $this->refresh() : true; // 1/5
        }

        return false;
    }

    public function forget()
    {
        if (session_id() === '') {
            return false;
        }

        $_SESSION = [];

        return session_destroy();
    }

    public function refresh()
    {
        return session_regenerate_id(true);
    }

    public function _read($id)
    {
        return $this->_decrypt(parent::read($id), $this->key);
    }

    public function _write($id, $data)
    {
        return parent::write($id, $this->_encrypt($data, $this->key));
    }

    public function isExpired($ttl = SESSION_TTL)
    {
        $activity = isset($_SESSION['_last_activity'])
            ? $_SESSION['_last_activity']
            : false;

        if ($activity !== false && time() - $activity > $ttl * 60) {
            return true;
        }

        $_SESSION['_last_activity'] = time();

        return false;
    }

    public function isFingerprint()
    {
        $hash = md5(
            $_SERVER['HTTP_USER_AGENT'] .
            (ip2long($_SERVER['REMOTE_ADDR']) & ip2long('255.255.0.0'))
        );

        if (isset($_SESSION['_fingerprint'])) {
            return $_SESSION['_fingerprint'] === $hash;
        }

        $_SESSION['_fingerprint'] = $hash;

        return true;
    }

    public function isValid($ttl = SESSION_TTL)
    {
        return ! $this->isExpired($ttl) && $this->isFingerprint();
    }

    public function get($name)
    {
        $parsed = explode('.', $name);

        $result = $_SESSION;

        while ($parsed) {
            $next = array_shift($parsed);

            if (isset($result[$next])) {
                $result = $result[$next];
            } else {
                return null;
            }
        }

        return $result;
    }

    public function put($name, $value)
    {
        $parsed = explode('.', $name);

        $session =& $_SESSION;

        while (count($parsed) > 1) {
            $next = array_shift($parsed);

            if ( ! isset($session[$next]) || ! is_array($session[$next])) {
                $session[$next] = [];
            }

            $session =& $session[$next];
        }

        $session[array_shift($parsed)] = $value;
    }
}

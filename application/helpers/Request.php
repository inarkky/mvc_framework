<?php

namespace application\helpers;

class Request {

    public static function protocol() {
        $secure = (self::server('HTTP_HOST') && self::server('HTTPS') && strtolower(self::server('HTTPS')) !== 'off');

        return $secure ? 'https' : 'http';
    }

    public static function isAjax() {
        return (self::server('HTTP_X_REQUESTED_WITH') && strtolower(self::server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
    }

    public static function method($upper = true) {
        $method = self::server('REQUEST_METHOD');

        return $upper ? strtoupper($method) : strtolower($method);
    }

    public static function referrer($default = null) {
        $referrer = self::server('HTTP_REFERER', $default);

        if ($referrer === null && $default !== null) {
            $referrer = $default;
        }

        return $referrer;
    }

    public static function server($index = '', $default = null) {
        return self::_findFromArray($_SERVER, $index, $default, false);
    }

    public static function get($item = null, $default = null, $xss_clean = true) {
        return self::_findFromArray($_GET, $item, $default, $xss_clean);
    }

    public static function post($item = null, $default = null, $xss_clean = true) {
        return self::_findFromArray($_POST, $item, $default, $xss_clean);
    }

    public static function request($item = null, $default = null, $xss_clean = true) {
        $request = array_merge($_GET, $_POST);

        return self::_findFromArray($request, $item, $default, $xss_clean);
    }

    public static function file($item = null, $default = null) {
        // If a file field was submitted without a file selected, this may still return a value.
        // It is best to use this method along with Input::hasFile()
        return self::_findFromArray($_FILES, $item, $default, false);
    }

    public static function inGet($item = null) {
        return self::get($item, null, false) !== null;
    }

    public static function inPost($item = null) {
        return self::post($item, null, false) !== null;
    }

    public static function inRequest($item = null) {
        return self::request($item, null, false) !== null;
    }

    public static function inFile($item = null) {
        return self::file($item) !== null;
    }

    public static function hasFile($item = null) {
        $file = self::file($item);

        return ($file !== null && $file['tmp_name'] !== '');
    }

    public static function xssClean($str = '') {
        // No data? We're done here
        if (is_string($str) && trim($str) === '') {
            return $str;
        }

        // Recursive sanitize if this is an array
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = self::xssClean($value);
            }

            return $str;
        }

        $str = str_replace(array(
            '&amp;',
            '&lt;',
            '&gt;'
        ), array(
            '&amp;amp;',
            '&amp;lt;',
            '&amp;gt;'
        ), $str);

        // Fix &entitiy\n;
        $str = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u', '$1;', $str);
        $str = preg_replace('#(&\#x*)([0-9A-F]+);*#iu', '$1$2;', $str);
        $str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');

        // remove any attribute starting with "on" or xmlns
        $str = preg_replace('#(<[^>]+[\x00-\x20\"\'\/])(on|xmlns)[^>]*>#iUu', '$1>', $str);

        // remove javascript
        $str = preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2nojavascript...', $str);
        $str = preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2novbscript...', $str);
        $str = preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*-moz-binding[\x00-\x20]*:#Uu', '$1=$2nomozbinding...', $str);
        $str = preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*data[\x00-\x20]*:#Uu', '$1=$2nodata...', $str);

        // Remove any style attributes, IE allows too much stupid things in them
        $str = preg_replace('#(<[^>]+[\x00-\x20\"\'\/])style[^>]*>#iUu', '$1>', $str);

        // Remove namespaced elements
        $str = preg_replace('#</*\w+:\w[^>]*>#i', '', $str);

        // Remove really unwanted tags
        do {
            $oldstring = $str;
            $str = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', '', $str);
        }
        while ($oldstring !== $str);

        return $str;
    }

    private static function _findFromArray($array = array(), $item = '', $default = null, $xss_clean = true) {
        if (empty($array)) {
            return $default;
        }

        if ( ! $item) {
            $arr = array();
            foreach (array_keys($array) as $key) {
                $arr[$key] = self::_fetchFromArray($array, $key, $default, $xss_clean);
            }
            return $arr;
        }

        return self::_fetchFromArray($array, $item, $default, $xss_clean);
    }

    private static function _fetchFromArray($array, $item = '', $default = null, $xss_clean = true) {
        if ( ! isset($array[$item])) {
            return $default;
        }

        if ($xss_clean) {
            return self::xssClean($array[$item]);
        }

        return $array[$item];
    }

}
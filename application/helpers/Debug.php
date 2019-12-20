<?php

namespace application\helpers;


use Throwable;

defined('E_DEPRECATED') || define('E_DEPRECATED', 8192);
defined('E_USER_DEPRECATED') || define('E_USER_DEPRECATED', 16384);

class Debug
{
    static private $time;
    static private $chrono;
    static private $registered;

    static protected $reporting = E_ALL;
    static protected $overload = array(
          '__callStatic'=> 2
        , '__call'      => 2
        , '__get'       => 1
        , '__set'       => 1
        , '__clone'     => 1
        , 'offsetGet'   => 1
        , 'offsetSet'   => 1
        , 'offsetUnset' => 1
        , 'offsetExists'=> 1
    );
    static protected $error = array(
          -1                    => 'Exception'
        , E_ERROR               => 'Fatal'
        , E_RECOVERABLE_ERROR   => 'Recoverable'
        , E_WARNING             => 'Warning'
        , E_PARSE               => 'Parse'
        , E_NOTICE              => 'Notice'
        , E_STRICT              => 'Strict'
        , E_DEPRECATED          => 'Deprecated'
        , E_CORE_ERROR          => 'Fatal'
        , E_CORE_WARNING        => 'Warning'
        , E_COMPILE_ERROR       => 'Compile Fatal'
        , E_COMPILE_WARNING     => 'Compile Warning'
        , E_USER_ERROR          => 'Fatal'
        , E_USER_WARNING        => 'Warning'
        , E_USER_NOTICE         => 'Notice'
        , E_USER_DEPRECATED     => 'Deprecated'
    );

    static public $style = array(
          'debug'       => 'font-size:1em;padding:.5em;border-radius:5px'
        , 'error'       => 'background:#eee'
        , 'exception'   => 'color:#825'
        , 'parse'       => 'color:#F07'
        , 'compile'     => 'color:#F70'
        , 'fatal'       => 'color:#F00'
        , 'recoverable' => 'color:#F22'
        , 'warning'     => 'color:#E44'
        , 'notice'      => 'color:#E66'
        , 'deprecated'  => 'color:#F88'
        , 'strict'      => 'color:#FAA'
        , 'stack'       => 'padding:.2em .8em;color:#444'
        , 'trace'       => 'border-left:1px solid #ccc;padding-left:1em'
        , 'scope'       => 'padding:.2em .8em;color:#666'
        , 'var'         => 'border-bottom:1px dashed #aaa;margin-top:-.5em;padding-bottom:.9em'
        , 'log'         => 'background:#f7f7f7;color:#33e'
        , 'chrono'      => 'border-left:2px solid #ccc'
        , 'init'        => 'color:#4A6'
        , 'time'        => 'color:#284'
        , 'table'       => 'color:#042'
    );


    public static function reporting($reporting = null)
    {
        if (!func_num_args()) {
            return self::$reporting;
        }
        self::$reporting = $reporting;
    }

    public static function log()
    {
        $args = func_get_args();
        self::$reporting &&
        print(
            self::style() . PHP_EOL . '<pre class="debug log">'
            . implode(
                '</pre>' . PHP_EOL . '<pre class="log">'
                , array_map(array(__CLASS__, 'var_export'), $args)
            )
            . '</pre>'
        );
    }

    public static function dump()
    {
        $args = func_get_args();
        self::$reporting &&
        die(Debug::log(...$args));
    }

    public static function chrono($print = null, $scope = '')
    {
        if (!self::$reporting) {return null;}
        if (!isset(self::$time[$scope])) {$chrono [] = '<b class="init">' . $scope . ' chrono init</b>';}
        elseif (is_string($print)) {
            $chrono[] = sprintf('<span class="time">%s -> %s: %fs</span>'
                , $scope
                , $print
                , round(self::$chrono[$scope][$print] = microtime(true) - self::$time[$scope], 6)
            );
        } elseif ($print && isset(self::$chrono[$scope])) {
            asort(self::$chrono[$scope]);
            $base = reset(self::$chrono[$scope]); // shortest duration
            foreach (self::$chrono[$scope] as $event => $duration) {
                $table[] = sprintf('%5u - %-38.38s <i>%7fs</i>'
                    , round($duration / $base, 2)
                    , $event
                    , round($duration, 3)
                );
            }
            $chrono[] = '<div class="table"><b>' . $scope . ' chrono table</b>' . PHP_EOL .
                sprintf('%\'-61s %-46s<i>duration</i>%1$s%1$\'-61s'
                    , PHP_EOL
                    , 'unit - action'
                ) .
                implode(PHP_EOL, $table) . PHP_EOL .
                '</div>';
        }
        echo self::style(), PHP_EOL, '<pre class="debug chrono">', implode(PHP_EOL, $chrono), '</pre>';
        return self::$time[$scope] = microtime(true);
    }

    public static function register($init = true)
    {
        if ($init) {
            if (!self::$registered) {
                self::$registered = array(
                    'display_errors'    => ini_get('display_errors')
                  , 'error_reporting'   => error_reporting()
                  , 'shutdown'          => register_shutdown_function('application\helpers\Debug::shutdown')
                );
            }
            self::$registered['shutdown'] = true;
            error_reporting(E_ALL);
            set_error_handler('application\helpers\Debug::handler', E_ALL);
            set_exception_handler('application\helpers\Debug::exception');
            ini_set('display_errors', 0);
        } elseif (self::$registered) {
            self::$registered['shutdown'] = false;
            error_reporting(self::$registered['error_reporting']);
            restore_error_handler();
            restore_exception_handler();
            ini_set('display_errors', self::$registered['display_errors']);
        }
    }

    public static function handler($type, $message, $file, $line, $scope, $stack = null)
    {
        global $php_errormsg;
        $php_errormsg = preg_replace('~^.*</a>\]: +(?:\([^\)]+\): +)?~', null, $message);
        $timestamp = date("Y-m-d H:i:s");

        error_log(sprintf('[%s] %s: %s in %s on line %s'.PHP_EOL
            , $timestamp
            , self::$error[$type] ?: 'Error'
            , $php_errormsg
            , $file
            , $line
        ), 3, LOGS_PATH . "Errors.log");

        if (!self::$reporting)
        {
            return false;
        }
        $stack = $stack ?: array_slice(debug_backtrace(false), ($type & E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE) ? 2 : 1);
        self::overload($stack, $file, $line); // switch line & file if overload method triggered the error
        echo self::style(), PHP_EOL, '<pre class="debug error ', strtolower(self::$error[$type]), '">', PHP_EOL,
        sprintf('<b>%s</b>: %s in <b>%s</b> on line <b>%s</b>'
            , self::$error[$type] ?: 'Error'
            , $php_errormsg
            , $file
            , $line
        );
        if ($type & self::$reporting)
        {
            echo self::context($stack, $scope);
        }
        echo '</pre>';
        if ($type & E_USER_ERROR) // well, fuck it.. fatal error
        {
            exit;
        }
    }

    public static function shutdown()
    {
        if (self::$registered['shutdown'] && ($error = error_get_last()) && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            self::handler($error['type'], $error['message'], $error['file'], $error['line'], null);
        }
    }

    public static function exception(Throwable $exception) //fuckin throwable.. don't change
    {
        $msg = sprintf('"%s" with message "%s"', get_class($exception), $exception->getMessage());
        self::handler(-1, $msg, $exception->getFile(), $exception->getLine(), null, $exception->getTrace());
        error_log(print_r($msg, TRUE), 3, LOGS_PATH . "Errors.log");
    }

    protected static function style()
    {
        static $style;
        if ($style) {
            return null;
        }
        foreach (self::$style as $class => $css) {
            $style .= sprintf('.%s{%s}', $class, $css);
        }
        return PHP_EOL . '<style type="text/css">' . $style . '</style>';
    }

    protected static function overload(&$stack, &$file, &$line)
    {
        if (isset($stack[0]['class'], self::$overload[$stack[0]['function']]) && $offset = self::$overload[$stack[0]['function']]) {
            for ($i = 0; $i < $offset; $i++) {
                extract(array_shift($stack));
            }
        } //TODO: overwrite file & line
    }

    protected static function context($stack, $scope)
    {
        if (!$stack) {
            return null;
        }
        $context[] = PHP_EOL . '<div class="stack"><i>Stack trace</i> :';
        foreach ($stack as $index => $call) {
            $context[] = sprintf('  <span class="trace">#%s %s: <b>%s%s%s</b>(%s)</span>'
                , $index
                , isset($call['file']) ? $call['file'] . ' (' . $call['line'] . ')' : '[internal function]'
                , isset($call['class']) ? $call['class'] : ''
                , isset($call['type']) ? $call['type'] : ''
                , $call['function']
                , isset($call['args']) ? self::args_export($call['args']) : ''
            );
        }
        $context[] = '  <span class="trace">#' . ($index + 1) . ' {main}</span>';
        $context[] = '</div><div class="scope"><i>Scope</i> :';
        $vars = '';
        if (isset($scope['GLOBALS'])) {
            $vars = '  GLOBAL';
        } elseif (!$scope) {
            $vars = '  NONE';
        } else {
            foreach ((array)$scope as $name => $value) {
                $vars .= '  <div class="var">$' . $name . ' = ' . self::var_export($value) . ';' . PHP_EOL . '</div>';
            }
        }
        $context[] = $vars . '</div>';
        return implode(PHP_EOL, $context);
    }

    protected static function var_export($var)
    {
        ob_start();
        var_dump($var);
        $export = ob_get_clean();
        $export = preg_replace('/\s*\bNULL\b/m',                  ' null',  $export); // Cleanup NULL
        $export = preg_replace('/\s*\bbool\((true|false)\)/m',    ' $1',    $export); // Cleanup booleans
        $export = preg_replace('/\s*\bint\((\d+)\)/m',            ' $1',    $export); // Cleanup integers
        $export = preg_replace('/\s*\bfloat\(([\d.e-]+)\)/mi',    ' $1',    $export); // Cleanup floats
        $export = preg_replace('/\s*\bstring\(\d+\) /m',          '',       $export); // Cleanup strings
        $export = preg_replace('/object\((\w+)\)(#\d+) \(\d+\)/m','$1$2',   $export); // Cleanup objects definition
        $export = preg_replace('/=>\s*/m',                        '=> ',    $export); // No new line between array/object keys and properties
        $export = preg_replace('/\[([\w": ]+)\]/',                ', $1 ',  $export); // remove square brackets in array/object keys
        $export = preg_replace('/([{(]\s+), /',                   '$1  ',   $export); // remove first coma in array/object properties listing
        $export = preg_replace('/\{\s+\}/m',                      '{}',     $export);
        $export = preg_replace('/\s+$/m',                         '',       $export); // Trim end spaces/new line
        return $export;
    }

    protected static function simple_export($var)
    {
        $export = self::var_export($var);
        if (is_array($var)) {
            $export = preg_replace('/\s+\d+ => /m', ', ', $export);
            $export = preg_replace('/\s+(["\w]+ => )/m', ', $1', $export);
            $pattern = '#array\(\d+\) \{[\s,]*([^{}]+|(?R))*?\s+\}#m';
            while (preg_match($pattern, $export)) {
                $export = preg_replace($pattern, 'array($1)', $export);
            }
            return $export;
        }
        return is_object($var) ? substr($export, 0, strpos($export, '#')) : $export;
    }

    protected static function args_export($args)
    {
        return implode(', ', array_map(
            'application\helpers\Debug::simple_export',
            (array)$args
        ));
    }
}
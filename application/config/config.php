<?php

//ENVIRONMENT dev/prod
define('ENVIRONMENT', 'dev');

//DB CONNECTIONS
define ('CONNECTIONS', [
    'default' => [
        'DB_TYPE' => 'mysql',
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '3306',
        'DB_NAME' => 'development',
        'DB_USER' => 'root',
        'DB_PASS' => 'root',
        'DB_CHARSET' => 'utf8'
    ],
    'B2B' => [
        'DB_TYPE' => 'mysql',
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '3306',
        'DB_NAME' => 'b2b',
        'DB_USER' => 'root',
        'DB_PASS' => 'root',
        'DB_CHARSET' => 'utf8'
    ]
]);

//EMAIL SETTINGS
//TODO: add email params

//WEB STRUCTURE
define('URL_PROTOCOL',      '//'); //protocol independent
define('URL_DOMAIN',        $_SERVER['HTTP_HOST']);
define('URL',               URL_PROTOCOL . URL_DOMAIN);
    define('PUBLIC_FOLDER_URL', URL_PROTOCOL . URL_DOMAIN . '/public/');
    define('RESOURCES_URL',     URL_PROTOCOL . URL_DOMAIN . '/application/resources/');
        define('CSS_URL',           RESOURCES_URL . 'css/');
        define('JS_URL',            RESOURCES_URL . 'js/');
        define('ASSETS_URL',        RESOURCES_URL . 'assets/');

//FILE STRUCTURE
define('APPLICATION_PATH',   ROOT_PATH              . 'application/');
    define('RESOURCES_PATH',    APPLICATION_PATH    . 'resources/');
        define('CSS_PATH',          RESOURCES_PATH  . 'css/');
        define('JS_PATH',           RESOURCES_PATH  . 'js/');
        define('ASSETS_PATH',       RESOURCES_PATH  . 'assets/');
        define('LANG_PATH',         RESOURCES_PATH  . 'lang/');
    define('STORAGE_PATH',      APPLICATION_PATH    . 'storage/');
        define('MEDIA_PATH',        STORAGE_PATH    . 'media/');
        define('CACHE_PATH',        STORAGE_PATH    . 'cache/');
        define('LOGS_PATH',         STORAGE_PATH    . 'logs/');
        define('SESSION_PATH',      STORAGE_PATH    . 'sessions/');
define('PUBLIC_PATH',        ROOT_PATH              . 'public/');
    define('COMPONENTS_PATH',   PUBLIC_PATH         . 'components/');
        define('HEAD_PATH',         COMPONENTS_PATH . 'head.php');
        define('HEADER_PATH',       COMPONENTS_PATH . 'header.php');
        define('FOOTER_PATH',       COMPONENTS_PATH . 'footer.php');
        define('NAVIGATION_PATH',   COMPONENTS_PATH . 'navigation.php');
        define('JS_INCLUDES_PATH',  COMPONENTS_PATH . 'js_includes.php');
        define('CSS_INCLUDES_PATH', COMPONENTS_PATH . 'css_includes.php');
define('DATABASE_PATH',     ROOT_PATH               . 'database/');
    define('MIGRATIONS_PATH',   DATABASE_PATH       . 'migrations/');
    define('SEEDS_PATH',        DATABASE_PATH       . 'seeds/');

//SECURITY
define('JWT_TOKEN', [
    'SECRET'          => 'DEF_0.9 Masa4Acc-Tok 9-sec',
    'REFRESH_SECRET'  => 'DEF_0.9 4ref Tok-sec',
    'DATA_SECRET'     => 'Int DEF/0.9 Tok-sec lev1',
    'MEDIA_SECRET'    => 'Int DEF/0.9 Tok-sec lev1',
    'INTERNAL_SECRET' => 'DEF_0.9 Int_tok tok-sec',
]);

//SESSION CONFIG
define('SESSION_KEY', 'bRuD5WYw5wd0rdHR9yLlM6wt2vteuiniQBqE70nAuhU=');
define('SESSION_TTL', 60);

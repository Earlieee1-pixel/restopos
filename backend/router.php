<?php

// Router para sa PHP built-in server — i-serve ang Laravel properly
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// I-serve ang static files kung naa sa public directory
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// I-route ang tanan requests sa Laravel front controller
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';

require_once __DIR__ . '/public/index.php';

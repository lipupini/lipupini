<?php

define('DIR_DOT', '.lipupini');

// Other core directory constants are defined in webroot `index.php`
define('DIR_ROOT', realpath(__DIR__ . '/../'));
define('DIR_COLLECTION', realpath(DIR_ROOT . '/collection'));
define('HOST', str_contains($_SERVER['HTTP_HOST'], ':') ? parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) : $_SERVER['HTTP_HOST']);

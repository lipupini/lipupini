<?php

require(__DIR__ . '/../../../package/vendor/autoload.php');

// Other core directory constants are defined in `system/Initialize.php`
define('DIR_WEBROOT', __DIR__);
define('DIR_VIEW', realpath(DIR_WEBROOT . '/../'));

error_reporting(E_ALL);
ini_set('display_errors', 1);

use System\Lipupini;

define('LIPUPINI_DEBUG', false);

(new Lipupini)
	->addPlugin(\Plugin\Lipupini\WebFinger::class)
	->addPlugin(\Plugin\Lipupini\LoadCollectionUrl::class)
	->addPlugin(\Plugin\Lipupini\CollectionHtml::class)
	->addPlugin(\Plugin\Lipupini\CollectionJson::class)
	->start();

<?php

require(__DIR__ . '/../../../package/vendor/autoload.php');

// Other core directory constants are defined in `system/Initialize.php`
define('DIR_WEBROOT', __DIR__);
define('DIR_VIEW', realpath(DIR_WEBROOT . '/../'));

error_reporting(E_ALL);
ini_set('display_errors', 1);

use System\Lipupini;

define('LIPUPINI_DEBUG', false);

// Set initial state from plugin
$state = require(DIR_PLUGIN . '/Lipupini/State.php');

(new Lipupini($state))
	->addPlugin(\Plugin\Lipupini\Collection\WebFinger::class)
	->addPlugin(\Plugin\Lipupini\Collection\Url::class)
	->addPlugin(\Plugin\Lipupini\Collection\Html::class)
	->addPlugin(\Plugin\Lipupini\Collection\Json::class)
	->start();

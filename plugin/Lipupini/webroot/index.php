<?php

require(__DIR__ . '/../../../package/vendor/autoload.php');

// Other core directory constants are defined in `system/Initialize.php`
define('DIR_WEBROOT', __DIR__);

error_reporting(E_ALL);
ini_set('display_errors', 1);

use System\Lipupini;

define('LIPUPINI_DEBUG', false);

// Set initial state from plugin
$state = require(DIR_PLUGIN . '/Lipupini/State.php');

return (new Lipupini($state))
	->addPlugin(\Plugin\Lipupini\Collection\WebFinger::class)
	->addPlugin(\Plugin\Lipupini\Collection\Url::class)
	->addPlugin(\Plugin\Lipupini\Collection\Render\Html::class)
	->addPlugin(\Plugin\Lipupini\Collection\Render\Atom::class)
	->addPlugin(\Plugin\Lipupini\Collection\Render\ActivityPubJson::class)
	->start();

<?php

require(__DIR__ . '/../../../package/vendor/autoload.php');

// Other core directory constants are defined in `system/Initialize.php`
define('DIR_WEBROOT', __DIR__);

error_reporting(E_ALL);
ini_set('display_errors', 1);

use System\Lipupini;

define('LIPUPINI_DEBUG', false);

// Set initial state from plugin
$state = new \Plugin\Lipupini\State;

return (new Lipupini($state))
	->addPlugin(\Plugin\Lukinview\HomepageHtml::class)
	->addPlugin(\Plugin\Lipupini\Collection\WebFinger::class)
	->addPlugin(\Plugin\Lipupini\Collection\Url::class)
	->addPlugin(\Plugin\Lipupini\Collection\Avatar::class)
	->addPlugin(\Plugin\Lukinview\Collection\Html::class)
	->addPlugin(\Plugin\Lukinview\Collection\Atom::class)
	->addPlugin(\Plugin\Lukinview\Collection\ActivityPubJson::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\Image::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\Video::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\Audio::class)
	->start();
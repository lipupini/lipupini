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
	->addPlugin(\Plugin\Lukinview\HomepagePlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\WebFingerPlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\UrlPlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\AvatarPlugin::class)
	->addPlugin(\Plugin\Lukinview\Collection\HtmlPlugin::class)
	->addPlugin(\Plugin\Lukinview\Collection\AtomPlugin::class)
	->addPlugin(\Plugin\Lukinview\Collection\ActivityPubJsonPlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\ImagePlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\VideoPlugin::class)
	->addPlugin(\Plugin\Lipupini\Collection\MediaProcessor\AudioPlugin::class)
	->start();

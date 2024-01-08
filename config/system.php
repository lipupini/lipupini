<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require(__DIR__ . '/../module/Lipupini/vendor/autoload.php');

$httpHost = php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST'];
$isHttps = !empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$baseUri = 'http' . ($isHttps ? 's' : '') . '://' . $httpHost . '/';

return new Module\Lipupini\State(
	baseUri: $baseUri, // Include trailing slash
	staticMediaBaseUri: $baseUri . 'c/', // If you'd like to use another URL for static files (e.g. CDN), put that here
	frontendModule: 'Lukinview',
	viewLanguage: 'english',
	requests: [
		// Once instantiated by Request\Incoming\Queue `render()`,
		// each key will hold the module instance itself
		Module\Lukinview\HomepageRequest::class => null,
		Module\Lipupini\WebFinger\Request::class => null,
		Module\Lipupini\ActivityPub\NodeInfoRequest::class => null,
		Module\Lipupini\Collection\Request::class => null,
		Module\Lipupini\ActivityPub\Request::class => null,
		Module\Lipupini\Rss\Request::class => null,
		Module\Lipupini\Collection\FolderRequest::class => null,
		Module\Lipupini\Collection\DocumentRequest::class => null,
		Module\Lipupini\Collection\AvatarRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\ImageRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\VideoPosterRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\VideoRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\TextRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\AudioRequest::class => null,
	],
	activityPubLog: false,
	debug: false
);

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
		// Once instantiated by Module\Lipupini\Request\Incoming\Queue `render()`,
		// each `requests` key here will instead hold the module instance itself
		Module\Lukinview\HomepageRequest::class => null,
		Module\Lipupini\WebFinger\Request::class => null,
		Module\Lipupini\ActivityPub\NodeInfoRequest::class => null,
		Module\Lipupini\Collection\Request::class => null, // This is where the collection folder name is determined for subsequent modules
		Module\Lipupini\Collection\MediaProcessor\Request\AudioRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\AvatarRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\ImageRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\TextRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\VideoPosterRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\VideoRequest::class => null,
		Module\Lipupini\Rss\Request::class => null, // This should be before the document/folder requests and after collection request
		Module\Lipupini\ActivityPub\Request::class => null, // This should be before the document/folder requests and after collection request
		Module\Lipupini\Collection\DocumentRequest::class => null,
		Module\Lipupini\Collection\FolderRequest::class => null,
	],
	mediaSizes: ['large' => [5000, 5000], 'small' => [600, 600]], // Default [width, height] for each preset
	activityPubLog: false,
	debug: false
);

<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use Module\Lipupini\State;

$isHttps = !empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$baseUri = 'http' . ($isHttps ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/';

return new State(
	baseUri: $baseUri, // Include trailing slash
	cacheBaseUri: $baseUri . 'c/', // If you'd like to use another URL for static files (e.g. CDN), put that here
	frontendView: 'Lukinview',
	requests: [
		Module\Lukinview\HomepageRequest::class => null,
		Module\Lipupini\WebFinger\Request::class => null,
		Module\Lipupini\ActivityPub\NodeInfoRequest::class => null,
		Module\Lipupini\Collection\Request::class => null,
		Module\Lipupini\Collection\FolderRequest::class => null,
		Module\Lipupini\Collection\DocumentRequest::class => null,
		Module\Lipupini\Collection\AvatarRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\ImageRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\VideoRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\MarkdownRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\AudioRequest::class => null,
		Module\Lipupini\Rss\Request::class => null,
		Module\Lipupini\ActivityPub\Request::class => null,
	],
	debug: true
);

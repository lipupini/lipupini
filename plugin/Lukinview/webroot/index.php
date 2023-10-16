<?php

require(__DIR__ . '/../../../package/vendor/autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$isHttps = !empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$systemState = new Plugin\Lipupini\State(
	baseUri: 'http' . ($isHttps ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/', // Include trailing slash
	frontendView: 'Lukinview',
	debug: true
);

return (new System\Lipupini(
	$systemState
))->requestQueue([
	"Plugin\\{$systemState->frontendView}\\HomepageRequest",
	Plugin\Lipupini\WebFinger\Request::class,
	Plugin\Lipupini\ActivityPub\NodeInfoRequest::class,
	Plugin\Lipupini\Collection\FolderRequest::class,
	Plugin\Lipupini\Collection\DocumentRequest::class,
	Plugin\Lipupini\Collection\AvatarRequest::class,
	Plugin\Lipupini\Collection\MediaProcessor\ImageRequest::class,
	Plugin\Lipupini\Collection\MediaProcessor\VideoRequest::class,
	Plugin\Lipupini\Collection\MediaProcessor\MarkdownRequest::class,
	Plugin\Lipupini\Collection\MediaProcessor\AudioRequest::class,
	Plugin\Lipupini\AtomRss\Request::class,
	Plugin\Lipupini\ActivityPub\Request::class,
])->render();

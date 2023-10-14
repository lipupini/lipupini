<?php

require(__DIR__ . '/../../../package/vendor/autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$isHttps = !empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$systemState = new System\State(
	dirWebroot: __DIR__,
	baseUri: 'http' . ($isHttps ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/', // Include trailing slash
	frontendView: 'Lukinview',
	debug: true
);

if (
	// Using PHP's builtin webserver, this will return a static file (e.g. CSS, JS, image) if it exists at the requested path
	php_sapi_name() === 'cli-server' &&
	$_SERVER['PHP_SELF'] !== '/index.php' &&
	file_exists($systemState->dirWebroot . $_SERVER['PHP_SELF'])
) {
	return false;
}

(new System\Lipupini(
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
	Plugin\Lipupini\ActivityPub\Request::class,
])->shutdown(function (System\State $systemStateShutdown) {
	http_response_code(404);
	echo '<pre>404 Not found' . "\n\n";
	echo $systemStateShutdown->executionTimeSeconds;
});

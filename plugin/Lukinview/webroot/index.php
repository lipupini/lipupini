<?php

require(__DIR__ . '/../../../package/vendor/autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$isHttps = !empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$systemState = new System\State(
	webrootDirectory: __DIR__,
	baseUri: 'http' . ($isHttps ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/', // Include trailing slash
	frontendView: 'Lukinview',
	debug: true
);

if (
	// Using PHP's builtin webserver, this will return a static file (e.g. CSS, JS, image) if it exists at the requested path
	php_sapi_name() === 'cli-server' &&
	$_SERVER['PHP_SELF'] !== '/index.php' &&
	file_exists($systemState->webrootDirectory . $_SERVER['PHP_SELF'])
) {
	return false;
}

(new System\Lipupini(
	$systemState
))->requestQueue([
	"Plugin\\{$systemState->frontendView}\\HomepageRequest",
	Plugin\Lipupini\WebFinger\Request::class,
	Plugin\Lipupini\ActivityPub\NodeInfo::class,
	Plugin\Lipupini\Collection\Request::class,
	Plugin\Lipupini\ActivityPub\Request::class,
])->shutdown(function (System\State $systemStateShutdown) {
	http_response_code(404);
	echo '<pre>404 Not found' . "\n\n";
	echo $systemStateShutdown->executionTimeSeconds;
});

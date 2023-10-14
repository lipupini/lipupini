<?php

$isHttps = !empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
return new System\State(
	dirWebroot: __DIR__ . '/webroot',
	baseUri: 'http' . ($isHttps ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/', // Include trailing slash
	frontendView: 'Lukinview',
	debug: true
);

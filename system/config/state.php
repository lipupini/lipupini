<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require(__DIR__ . '/../../module/Lipupini/vendor/autoload.php');

$httpHost = php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST'];
$isHttps = !empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$baseUri = 'http' . ($isHttps ? 's' : '') . '://' . $httpHost . '/';

return new Module\Lipupini\State(
	baseUri: $baseUri, // Include trailing slash
	staticMediaBaseUri: $baseUri . 'c/', // If you'd like to use another URL for static files (e.g. CDN), put that here
	frontendModule: 'Lukinview',
	viewLanguage: 'english',
	request: [
		// Once instantiated by Module\Lipupini\Request\Incoming\Queue `render()`,
		// each `request` key here will instead hold the module instance itself
		Module\Lukinview\HomepageRequest::class => null,
		Module\Lipupini\WebFinger\Request::class => null,
		Module\Lipupini\ActivityPub\NodeInfoRequest::class => null,
		Module\Lipupini\Collection\Request::class => null, // This is where the collection folder name is determined for subsequent modules
		Module\Lipupini\Collection\MediaProcessor\Request\AudioRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\AvatarRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\ImageRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\TextRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\VideoThumbnailRequest::class => null,
		Module\Lipupini\Collection\MediaProcessor\Request\VideoRequest::class => null,
		Module\Lipupini\Rss\Request::class => null, // This should be before the document/folder requests and after collection request
		Module\Lipupini\ActivityPub\Request::class => null, // This should be before the document/folder requests and after collection request
		Module\Lipupini\Collection\DocumentRequest::class => null,
		Module\Lipupini\Collection\FolderRequest::class => null,
	],
	mediaSize: ['large' => [5000, 5000], 'thumbnail' => [600, 600]], // Default [width, height] for each preset
	mediaType: [
		'audio' => [
			'flac' => 'audio/flac',
			'm4a' => 'audio/m4a',
			'mp3' => 'audio/mp3',
			'ogg' => 'audio/ogg',
		],
		'image' => [
			'avif' => 'image/avif',
			'gif' => 'image/gif',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
		],
		'text' => [
			'html' => 'text/html',
			'md' => 'text/markdown',
		],
		'video' => [
			'mp4' => 'video/mp4',
		],
	],
	imageQuality: ['avif_quality' => 69, 'jpeg_quality' => 86, 'png_compression_level' => 9],
	useFfmpeg: true, // You can try this if you have `ffmpeg` installed for processing videos
	activityPubLog: false,
	debug: false
);

<?php

namespace Module\Lipupini;

use Module\Lipupini\L18n\A;

class State {
	public float $microtimeInit = -1; // Set automatically
	public float $executionTimeSeconds = -1; // Set automatically
	public string|null $responseContent = null; // Final output to browser/client
	public string $baseUriPath = '/'; // Set automatically based on `baseUri` in `system/config/state.php`
	public string $host  = 'null.localhost'; // Set automatically based on `baseUri` in `system/config/state.php`

	public function __construct(
		// Using `/dev/null` for a default value seems safer than using an empty string
		public string $dirWebroot         = '/dev/null', // Location of `index.php`, set in `__construct`
		public string $dirRoot            = '/dev/null', // Root directory of the application, set in `__construct`
		public string $dirModule          = '/dev/null', // Module directory, set in `__construct`
		public string $dirCollection      = '/dev/null', // Collection directory, set in `__construct`
		public string $baseUri            = 'http://dev.null/', // Be sure this has a trailing slash. Should be full URI e.g. https://example.org/~basePath/
		public string $staticMediaBaseUri = 'http://dev.null/c/', // Also has a trailing slash
		public string $frontendModule     = 'Lukinview',
		public string $viewLanguage       = 'english',
		public string $userAgent          = '(Lipupini/69.420; +https://github.com/lipupini/lipupini)',
		public array  $request           = [], // Request queue for `Module\Lipupini\Request\Incoming\Queue`
		public array  $mediaSize          = ['large' => [5000, 5000], 'thumbnail' => [600, 600]], // Default [width, height] for each preset
		public array  $mediaType          = [
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
		public array  $imageQuality       = ['avif_quality' => 60, 'jpeg_quality' => 89, 'png_compression_level' => 9],
		public bool   $useFfmpeg          = true, // You can try this if you have `ffmpeg` installed for processing videos
		public bool   $activityPubLog     = true,
		public bool   $shutdown           = false,
		public bool   $debug              = false,
	) {
		session_start();

		if ($this->baseUri === 'http://dev.null/') {
			throw new Exception('`baseUri` is required');
		}

		$parsedUri = parse_url($this->baseUri);
		$this->baseUriPath = $parsedUri['path'];
		$this->host = $parsedUri['host'] . (empty($parsedUri['port']) ? '' : ':' . $parsedUri['port']);

		if ($staticMediaBaseUri === 'http://dev.null/c/') {
			$this->staticMediaBaseUri = $this->baseUriPath . 'c/';
		}

		if ($this->dirRoot === '/dev/null') {
			$this->dirRoot = realpath(__DIR__ . '/../../');
		}

		if ($this->dirModule === '/dev/null') {
			$this->dirModule = $this->dirRoot . '/module';
		}

		if ($this->dirWebroot === '/dev/null') {
			$this->dirWebroot = $this->dirModule . '/' . $this->frontendModule . '/webroot';
		}

		if ($this->dirCollection === '/dev/null') {
			$this->dirCollection = $this->dirRoot . '/collection';
		}

		// For security reasons, a completely random version number is always statically served. Lipupini should
		// not change anything about the ActivityPub protocol, therefore the version is irrelevant to other
		// instances and particularly to any instance that may suspect that the version number is relevant.
		if ($this->userAgent === '(Lipupini/69.420; +https://github.com/lipupini/lipupini)') {
			$this->userAgent = '(Lipupini/69.420; +' . $this->baseUri . ')';
		}

		A::initializeViewLanguages($this);

		$this->microtimeInit = microtime(true);
	}
}

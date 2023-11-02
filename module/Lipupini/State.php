<?php

namespace Module\Lipupini;

use Module\Lipupini\L18n\A;

class State {
	public float $microtimeInit = -1; // Set automatically
	public float $executionTimeSeconds = -1; // Set automatically
	public string|null $responseContent = null; // Final output to browser/client
	public string $baseUriPath = '/'; // Set automatically in `__construct()` by specifying `baseUri`
	public string $host  = 'null.localhost'; // Set automatically in `__construct()` by specifying `baseUri`

	public function __construct(
		public string $dirWebroot         = '/dev/null', // Reasonably safe default, this is set after instantiation
		public string $dirRoot            = '/dev/null',
		public string $dirModule          = '/dev/null',
		public string $dirCache         = '/dev/null',
		public string $dirCollection      = '/dev/null',
		public string $baseUri            = 'http://dev.null/', // Be sure this has a trailing slash. Should be full URI e.g. https://example.org/~basePath/
		public string $staticMediaBaseUri = 'http://dev.null/c/', // Also has a trailing slash
		public string $frontendModule     = 'Lukinview',
		public string $viewLanguage       = 'english',
		public string $userAgent          = '(Lipupini/69.420; +https://github.com/instalution/lipupini)',
		public array  $requests           = [],
		public bool   $shutdown           = false,
		public bool   $debug              = false
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

		if ($this->dirCache === '/dev/null') {
			$this->dirCache = $this->dirRoot . '/cache';
		}

		if ($this->dirCollection === '/dev/null') {
			$this->dirCollection = $this->dirRoot . '/collection';
		}

		// For security reasons, a completely random version number is always statically served. Lipupini should
		// not change anything about the ActivityPub protocol, therefore the version is irrelevant to other
		// instances and particularly to any instance that may suspect that the version number is relevant.
		if ($this->userAgent === '(Lipupini/69.420; +https://github.com/instalution/lipupini)') {
			$this->userAgent = '(Lipupini/69.420; +' . $this->baseUri . ')';
		}

		A::initializeViewLanguages($this);

		$this->microtimeInit = microtime(true);
	}
}

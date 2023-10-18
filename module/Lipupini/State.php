<?php

namespace Module\Lipupini;

class State {
	public float $microtimeInit = -1;
	public float $executionTimeSeconds = -1;
	public string|null $responseContent = null;

	public function __construct(
		public string $dirWebroot = '/dev/null', // Reasonably safe default, this is set after instantiation
		public string $dirRoot = '/dev/null', // Reasonably safe default, this is set after instantiation
		public string $dirModule = '/dev/null', // Reasonably safe default, this is set after instantiation
		public string $dirStorage = '/dev/null', // Reasonably safe default, this is set after instantiation
		public string $dirCollection = '/dev/null', // Reasonably safe default, this is set after instantiation
		public string $host = 'null.localhost',
		public string $baseUri = 'http://dev.null/', // Be sure this has a trailing slash. Should be full URI e.g. https://example.org/~basePath/
		public string $cacheBaseUri = 'http://dev.null/c/',
		public string $baseUriPath = '/',
		public string $frontendView = 'Lukinview',
		public array $requests = [],
		public bool $shutdown = false,
		public bool $debug = false
	) {
		if ($baseUri === 'http://dev.null/') {
			throw new Exception('`baseUri` is required');
		}

		$parsedUri = parse_url($this->baseUri);

		if ($this->host === 'null.localhost') {
			$this->host = $parsedUri['host'] . (empty($parsedUri['port']) ? '' : ':' . $parsedUri['port']);
		}

		if ($this->baseUriPath === '/dev/null') {
			$this->baseUriPath = $parsedUri['path'];
		}

		if ($cacheBaseUri === 'http://dev.null/c/') {
			$this->cacheBaseUri = $this->baseUriPath . 'c/';
		}

		if ($this->dirRoot === '/dev/null') {
			$this->dirRoot = realpath(__DIR__ . '/../../');
		}

		if ($this->dirCollection === '/dev/null') {
			$this->dirCollection = $this->dirRoot . '/collection';
		}

		if ($this->dirModule === '/dev/null') {
			$this->dirModule = $this->dirRoot . '/module';
		}

		if ($this->dirWebroot === '/dev/null') {
			$this->dirWebroot = $this->dirModule . '/' . $this->frontendView . '/webroot';
		}

		if ($this->dirStorage === '/dev/null') {
			$this->dirStorage = $this->dirRoot . '/storage';
		}

		$this->microtimeInit = microtime(true);
	}
}

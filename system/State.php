<?php

namespace System;

class State {
	public float $microtimeInit = -1;
	public float $executionTimeSeconds = -1;

	public function __construct(
		public string $webrootDirectory, // Reasonably safe default
		public string $baseUri = 'http://dev.null/', // Be sure this has a trailing slash. Should be full URL e.g. https://example.org/~basePath/
		public string $baseUriPath = '/',
		public array $requests = [],
		public string $host = 'null.localhost',
		public string $dirRoot = '/dev/null', // Reasonably safe default, this is set after instantiation
		public string $dirPlugin = '/dev/null', // Reasonably safe default, this is set after instantiation
		public string $dirStorage = '/dev/null', // Reasonably safe default, this is set after instantiation
		public string $dirCollection = '/dev/null', // Reasonably safe default, this is set after instantiation
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

		if ($this->dirRoot === '/dev/null') {
			$this->dirRoot = realpath(__DIR__ . '/../');
		}

		if ($this->dirCollection === '/dev/null') {
			$this->dirCollection = $this->dirRoot . '/collection';
		}

		if ($this->dirPlugin === '/dev/null') {
			$this->dirPlugin = $this->dirRoot . '/plugin';
		}

		if ($this->dirStorage === '/dev/null') {
			$this->dirStorage = $this->dirRoot . '/storage';
		}

		$this->microtimeInit = microtime(true);
	}
}

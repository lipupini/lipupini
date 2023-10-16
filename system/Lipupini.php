<?php

namespace System;

class Lipupini {
	private bool $serveStaticRequest = false;

	public function __construct(protected State $system) {
		if (
			// Using PHP's builtin webserver, this will return a static file (e.g. CSS, JS, image) if it exists at the requested path
			php_sapi_name() === 'cli-server' &&
			$_SERVER['PHP_SELF'] !== '/index.php' &&
			file_exists($this->system->dirWebroot . $_SERVER['PHP_SELF'])
		) {
			$this->serveStaticRequest = true;
		}
	}

	public function requestQueue(array $requestClasses): self {
		if ($this->serveStaticRequest) {
			return $this;
		}

		if ($this->system->debug) {
			error_log('Remote request details:');
			error_log(print_r($_REQUEST, true));
			error_log(print_r($_SERVER, true));
			error_log(print_r(file_get_contents('php://input'), true));
		}

		foreach ($requestClasses as $requestClassName) {
			$this->loadRequestPlugin($requestClassName);
			if ($this->system->shutdown) {
				return $this;
			}
		}

		return $this;
	}

	public function loadRequestPlugin(string $requestClassName): void {
		if (array_key_exists($requestClassName, $this->system->requests)) {
			throw New Exception('Already loaded request: ' . $requestClassName);
		}

		if (!class_exists($requestClassName)) {
			throw new Exception('Could not load plugin: ' . $requestClassName);
		}

		$request = new $requestClassName($this->system);

		$this->system->requests[$requestClassName] = $request;
	}

	public function render(callable $then = null): bool {
		if ($this->serveStaticRequest) {
			return false;
		}

		$microtimeLater = microtime(true);
		$this->system->executionTimeSeconds = $microtimeLater - $this->system->microtimeInit;

		header('X-Powered-By: Lipupini');

		if ($this->system->debug) {
			header('Server-Timing: app;dur=' . $this->system->executionTimeSeconds);
		}

		if (is_callable($then)) {
			$then($this->system);
		}

		if (is_null($this->system->responseContent)) {
			http_response_code(404);
			echo '<pre>404 Not found' . "\n\n";
			echo $this->system->executionTimeSeconds;
		} else {
			echo $this->system->responseContent;
		}

		return true;
	}
}

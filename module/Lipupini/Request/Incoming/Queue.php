<?php

namespace Module\Lipupini\Request\Incoming;

use Module\Lipupini\State;

class Queue {
	private bool $serveStaticRequest = false;

	public function __construct(protected State $system) {
		if (
			// Using PHP's builtin webserver, this will return a static file (e.g. CSS, JS, image) if it exists at the requested path
			$_SERVER['PHP_SELF'] !== '/index.php' &&
			PHP_SAPI === 'cli-server' &&
			file_exists($this->system->dirWebroot . $_SERVER['PHP_SELF'])
		) {
			$this->serveStaticRequest = true;
			return;
		}

		if ($this->system->debug) {
			error_log('Incoming request details:');
			error_log(print_r($_REQUEST, true));
			error_log(print_r($_SERVER, true));
			error_log(print_r(file_get_contents('php://input'), true));
		}
	}

	public function processRequestQueue(): void {
		if ($this->serveStaticRequest) {
			return;
		}

		foreach ($this->system->request as $requestClassName => $initialState) {
			$this->loadRequestModule($requestClassName);
			if ($this->system->shutdown) {
				return;
			}
		}
	}

	public function loadRequestModule(string $requestClassName): void {
		if (
			array_key_exists($requestClassName, $this->system->request) &&
			!is_null($this->system->request[$requestClassName])
		) {
			throw New Exception('Already loaded request: ' . $requestClassName);
		}

		if (!class_exists($requestClassName)) {
			throw new Exception('Could not load module: ' . $requestClassName);
		}

		$request = new $requestClassName($this->system);

		$this->system->request[$requestClassName] = $request;
	}

	public function render(): bool {
		if ($this->serveStaticRequest) {
			return false;
		}

		$this->processRequestQueue();

		$microtimeLater = microtime(true);
		$this->system->executionTimeSeconds = $microtimeLater - $this->system->microtimeInit;

		header('X-Powered-By: Lipupini');

		if ($this->system->debug) {
			header('Server-Timing: app;dur=' . $this->system->executionTimeSeconds);
		}

		if (is_null($this->system->responseContent)) {
			http_response_code(404);
			echo '<pre>404 Not found' . "\n\n";
			if ($this->system->debug) {
				echo '<!-- ' . $this->system->executionTimeSeconds . ' -->';
			}
		} else {
			echo $this->system->responseContent;
		}

		return true;
	}
}

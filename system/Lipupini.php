<?php

namespace System;

class Lipupini {
	public function __construct(protected State $system) { }

	public function requestQueue(array $requestClasses): self {
		foreach ($requestClasses as $requestClassName) {
			$this->loadRequestPlugin($requestClassName);
			if ($this->system->shutdown) {
				$this->shutdown();
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

	public function shutdown(callable $callback = null): void {
		$microtimeLater = microtime(true);
		$this->system->executionTimeSeconds = $microtimeLater - $this->system->microtimeInit;

		//header('X-Powered-By: Lipupini');

		if ($this->system->debug) {
			//header('Server-Timing: app;dur=' . $this->system->executionTimeSeconds);
		}

		if (is_callable($callback)) {
			$callback($this->system);
		}

		exit();
	}
}

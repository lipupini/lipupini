<?php

namespace Plugin\Lipupini\Http;

use System\State;

abstract class Request {
	public function __construct(protected State $system) {
		if ($this->system->debug) {
			error_log('DEBUG: Starting request plugin ' . get_called_class());
		}

		$this->initialize();
	}

	abstract public function initialize(): void;

	public static function clientAcceptsMimeTypes(array $relevantAcceptsMimes): bool {
		// HTTP Accept header needs to be preset to proceed
		if (empty($_SERVER['HTTP_ACCEPT'])) {
			return false;
		}

		// Can be comma-separated list so make it an array
		$clientAcceptsMimes = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT']));

		if (count($clientAcceptsMimes) > 20) {
			throw new Exception('Suspicious number of client accept MIMEs');
		}

		$matchedMime = false;

		foreach ($clientAcceptsMimes as $mime) {
			if (in_array($mime, $relevantAcceptsMimes, true)) {
				$matchedMime = true;
				break;
			}
		}

		return $matchedMime;
	}
}

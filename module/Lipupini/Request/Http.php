<?php

namespace Module\Lipupini\Request;

use Module\Lipupini\State;

abstract class Http {
	public function __construct(public State $system) {
		if ($this->system->debug) {
			error_log('DEBUG: Starting request module ' . get_called_class());
		}

		$this->initialize();
	}

	abstract public function initialize(): void;

	public static function validateRequestMimeTypes(string $_SERVER_KEY, array $relevantAcceptsMimes): bool {
		// HTTP Accept header needs to be preset to proceed
		if (empty($_SERVER[$_SERVER_KEY])) {
			return false;
		}

		// Can be comma-separated list so make it an array
		$clientAcceptsMimes = array_map('trim', explode(',', $_SERVER[$_SERVER_KEY]));

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

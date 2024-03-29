<?php

namespace Module\Lipupini\Request\Incoming;

use Module\Lipupini\State;

abstract class Http {
	public function __construct(public State $system) {
		if ($this->system->debug) {
			error_log('DEBUG: Starting request module ' . get_called_class());
		}

		$this->initialize();
	}

	abstract public function initialize(): void;
}

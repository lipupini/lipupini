<?php

namespace Module\Lipupini\ActivityPub;

class Exception extends \Module\Lipupini\Exception {
	public function __toString() {
		http_response_code($this->getCode());
		return json_encode([
			'error' => $this->getMessage(),
			'code' => $this->getCode(),
		]);
	}
}

<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\State;
use Module\Lipupini\Collection;

class Cache {
	private string $path;

	public function __construct(private State $system, protected string $collectionFolderName) {
		$path = $this->system->dirCollection . '/' . $this->collectionFolderName . '/.lipupini/cache';

		if (!is_dir($path)) {
			mkdir($path, 0755, true);
		}

		$this->path = $path;
	}

	public function path() {
		return $this->path;
	}
}

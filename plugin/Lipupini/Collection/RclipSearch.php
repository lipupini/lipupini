<?php

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini\Exception;
use System\Lipupini;

class RclipSearch {
	public function __construct(
		public string $collectionFolderName,
		public string $rclipPath,
		public int $numResults = 50
	) {
		Lipupini::validateCollectionFolderName($collectionFolderName);
		if (!file_exists($this->rclipPath)) {
			throw new Exception('Could not find `rclip`');
		}
		if (!is_executable($this->rclipPath)) {
			throw new Exception('`rclip` bin is not executable');
		}
	}

	public function query($query) {
		$this->rclipCommand($query, '--filepath-only --no-indexing --top=' . (int)$this->numResults);
	}

	public function buildIndex() {
		$this->rclipCommand('*');
	}

	private function getRclipDataDir() {
		return DIR_COLLECTION . '/' . $this->collectionFolderName . '/.lipupini/.rclip';
	}

	private function rclipCommand(string $query, string $flags = '') {
		$command = 'cd ' . escapeshellarg(DIR_COLLECTION . '/' . $this->collectionFolderName) . ' &&';
		$command .= ' RCLIP_DATADIR=' . escapeshellarg($this->getRclipDataDir());
		$command .= ' ' . escapeshellcmd($this->rclipPath);
		$command .= ' ' . escapeshellarg($query);
		$command .= ' --exclude-dir=.lipupini';
		if ($flags) {
			$command .= ' ' . $flags;
		}
		passthru($command);
	}
}

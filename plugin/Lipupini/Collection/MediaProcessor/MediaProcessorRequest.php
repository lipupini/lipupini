<?php

namespace Plugin\Lipupini\Collection\MediaProcessor;

use Plugin\Lipupini\Http;

abstract class MediaProcessorRequest extends Http\Request {
	abstract static public function mimeTypes(): array;

	public function cacheAndServe(string $filePath, string $mimeType): void {
		if (!file_exists($filePath)) {
			http_response_code(404);
			echo 'Not found';
			return;
		}

		if (!is_dir($this->system->dirWebroot . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME))) {
			mkdir($this->system->dirWebroot . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME), 0755, true);
		}

		copy($filePath, $this->system->dirWebroot . $_SERVER['REQUEST_URI']);

		header('Content-type: ' . $mimeType);
		readfile($this->system->dirWebroot . $_SERVER['REQUEST_URI']);
	}
}

<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Request\Incoming\Http;

abstract class MediaProcessorRequest extends Http {
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
		$this->system->responseContent = file_get_contents($filePath);
	}
}

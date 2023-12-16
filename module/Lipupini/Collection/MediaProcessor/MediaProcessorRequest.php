<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Request\Incoming\Http;

abstract class MediaProcessorRequest extends Http {
	abstract static public function mimeTypes(): array;

	public function symlinkAndServe(string $filePath, string $mimeType): void {
		if (!file_exists($filePath)) {
			http_response_code(404);
			echo 'Not found';
			return;
		}

		if (!is_dir($this->system->dirWebroot . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME))) {
			mkdir($this->system->dirWebroot . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME), 0755, true);
		}

		if (!file_exists($filePath)) {
			symlink($filePath, $this->system->dirWebroot . $_SERVER['REQUEST_URI']);
		}

		header('Content-type: ' . $mimeType);
		// With the possibility of very large files and potential issues with static file serving, we are not using the `$this->system->responseContent` option here
		readfile($filePath);
		exit();
	}
}

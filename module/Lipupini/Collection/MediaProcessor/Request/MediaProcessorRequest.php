<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Request\Incoming\Http;

abstract class MediaProcessorRequest extends Http {
	public function serve(string $filePath, string $mimeType): void {
		if (!file_exists($filePath)) {
			return;
		}

		header('Content-type: ' . $mimeType);
		// With the possibility of very large files and potential issues with static file serving, we are not using the `$this->system->responseContent` option here
		readfile($filePath);
		exit();
	}
}

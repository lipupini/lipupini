<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Request\Incoming\Http;

abstract class MediaProcessorRequest extends Http {
	public function serve(string $filePath, string $mimeType): void {
		if (!$filePath || !file_exists($filePath)) {
			return;
		}

		header('Content-type: ' . $mimeType);
		// With the possibility of very large files, and even though a static file is supposed to be served after caching,
		// we are not using the `$this->system->responseContent` option here and going with `readfile` for media
		readfile($filePath);
		exit();
	}
}

<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Request\Incoming\Http;
use Module\Lipupini\State;

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

	public static function relativeStaticCachePath(State $systemState) {
		return parse_url($systemState->staticMediaBaseUri, PHP_URL_PATH);
	}
}

<?php

namespace Lipupini\Collection\MediaProcessor\Request;

use Lipupini\Collection\Trait\CollectionRequest;
use Lipupini\Request\Queued;
use Lipupini\State;

abstract class MediaProcessorRequest extends Queued {
	use CollectionRequest;

	public static function relativeStaticCachePath(State $systemState) {
		return parse_url($systemState->staticMediaBaseUri, PHP_URL_PATH);
	}

	public function serve(string $filePath, string $mimeType): void {
		if (!$filePath || !file_exists($filePath)) {
			return;
		}

		$expiresOffset = 86400; // 1 day
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expiresOffset) . ' GMT');
		header('Cache-Control: public, max-age=' . $expiresOffset);
		header('Content-type: ' . $mimeType);
		// With the possibility of very large files, and even though a static file is supposed to be served after caching,
		// we are not using the `$this->system->responseContent` option here and going with `readfile` for media
		readfile($filePath);
		exit();
	}

	public function validateMediaProcessorRequest() {
		$relativeStaticCachePath = static::relativeStaticCachePath($this->system);
		if (!str_starts_with($_SERVER['REQUEST_URI_DECODED'], $relativeStaticCachePath)) return false;
		$this->collectionNameFromSegment(1, '', $relativeStaticCachePath);

		return preg_replace(
			'#^' . preg_quote($relativeStaticCachePath) . preg_quote($this->collectionName) . '/#',
			'',
			$_SERVER['REQUEST_URI_DECODED']
		);
	}
}

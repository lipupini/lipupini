<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Text;

class TextRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!($mediaRequest = $this->validateMediaProcessorRequest())) return;

		if (!preg_match('#^text/(html|markdown)/(.+\.(' . implode('|', array_keys($this->system->mediaType['text'])) . '))$#', $mediaRequest, $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$outputType = $matches[1];
		$filePath = $matches[2];
		$extension = $matches[3];

		if ($extension === 'html') {
			if ($outputType !== 'html') {
				throw new Exception('File path mismatch: ' . $_SERVER['REQUEST_URI_DECODED']);
			}
			$mdFilePath = preg_replace('#\.html$#', '', $filePath);
		} else {
			if ($outputType !== 'markdown') {
				throw new Exception('File path mismatch: ' . $_SERVER['REQUEST_URI_DECODED']);
			}
			$mdFilePath = $_SERVER['REQUEST_URI_DECODED'];
		}

		$pathOriginal = $this->system->dirCollection . '/' . $this->collectionName . '/' . $mdFilePath;

		(new Collection\Utility($this->system))->validateCollectionName($this->collectionName);

		if (!file_exists($pathOriginal)) {
			return;
		}

		$this->system->responseType = $this->system->mediaType['text'][$extension];
		$this->system->responseContent = file_get_contents(
			Text::processAndCache($this->system, $this->collectionName, 'text', $mdFilePath)
		);
	}
}

<?php

namespace Module\Lipupini\Collection\MediaProcessor\Request;

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor\Text;

class TextRequest extends MediaProcessorRequest {
	public function initialize(): void {
		if (!preg_match('#^/c/([^/]+)/text/(.+\.(' . implode('|', array_keys($this->system->mediaType['text'])) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$filePath = $matches[2];
		$extension = $matches[3];

		if ($extension === 'html') {
			$mdFilePath = urldecode(preg_replace('#\.html$#', '', $filePath));
		} else {
			$mdFilePath = urldecode($_SERVER['REQUEST_URI'] . '.html');
		}

		$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . '/' . $mdFilePath;

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		if (!file_exists($pathOriginal)) {
			return;
		}

		$this->system->responseType = $this->system->mediaType['text'][$extension];
		$this->system->responseContent = file_get_contents(
			Text::processAndCache($this->system, $collectionFolderName, 'text', $mdFilePath)
		);
	}
}

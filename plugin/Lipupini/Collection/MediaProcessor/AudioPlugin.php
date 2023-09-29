<?php

namespace Plugin\Lipupini\Collection\MediaProcessor;

use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class AudioPlugin extends Plugin {
	public function start(State $state): State {
		$extMimes = [
			'mp3' => 'audio/mp3',
		];

		if (!preg_match('#^/c/file/([^/]+)/(large)/(.+(' . implode('|', array_keys($extMimes)) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return $state;
		}

		// If the URL has matched, we're going to shutdown after this plugin returns no matter what
		$state->lipupiniMethod = 'shutdown';

		$collectionFolderName = $matches[1];
		$filePath = $matches[3];
		$extension = $matches[4];

		Lipupini::validateCollectionFolderName($collectionFolderName);

		$pathOriginal = DIR_COLLECTION . '/' . $collectionFolderName . '/' . $filePath;

		if (!file_exists($pathOriginal)) {
			http_response_code(404);
			echo 'Not found';
			return $state;
		}

		if (!is_dir(DIR_WEBROOT . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME))) {
			mkdir(DIR_WEBROOT . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME), 0755, true);
		}

		copy($pathOriginal, DIR_WEBROOT . $_SERVER['REQUEST_URI']);

		header('Content-type: ' . $extMimes[$extension]);
		readfile(DIR_WEBROOT . $_SERVER['REQUEST_URI']);

		return $state;
	}
}

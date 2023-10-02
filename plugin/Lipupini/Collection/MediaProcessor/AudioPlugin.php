<?php

namespace Plugin\Lipupini\Collection\MediaProcessor;

use Plugin\Lipupini\Collection;
use Plugin\Lipupini\State;
use System\Plugin;

class AudioPlugin extends Plugin {
	public static array $extMimes = [
		'mp3' => 'audio/mp3',
	];

	public function start(State $state): State {
		if (!preg_match('#^/c/file/([^/]+)/(large)/(.+\.(' . implode('|', array_keys(static::$extMimes)) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return $state;
		}

		// If the URL has matched, we're going to shutdown after this plugin returns no matter what
		$state->lipupiniMethod = 'shutdown';

		$collectionFolderName = $matches[1];
		$filePath = $matches[3];
		$extension = $matches[4];

		Collection\Utility::validateCollectionFolderName($collectionFolderName);

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

		header('Content-type: ' . static::$extMimes[$extension]);
		readfile(DIR_WEBROOT . $_SERVER['REQUEST_URI']);

		return $state;
	}
}

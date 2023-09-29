<?php

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class AvatarPlugin extends Plugin {
	public function start(State $state): State {
		$extMimes = [
			'png' => 'image/png',
		];

		if (!preg_match('#^/c/avatar/([^/]+)(\.(' . implode('|', array_keys($extMimes)) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return $state;
		}

		// If the URL has matched, we're going to shutdown after this plugin returns no matter what
		$state->lipupiniMethod = 'shutdown';

		$collectionFolderName = $matches[1];
		$extension = $matches[3];

		Lipupini::validateCollectionFolderName($collectionFolderName);

		$avatarPath = DIR_COLLECTION . '/' . $collectionFolderName . '/.lipupini/.avatar.png';

		if (!file_exists($avatarPath)) {
			http_response_code(404);
			echo 'Not found';
			return $state;
		}

		if (!is_dir(DIR_WEBROOT . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME))) {
			mkdir(DIR_WEBROOT . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME), 0755, true);
		}

		copy($avatarPath, DIR_WEBROOT . $_SERVER['REQUEST_URI']);

		header('Content-type: ' . $extMimes[$extension]);
		readfile(DIR_WEBROOT . $_SERVER['REQUEST_URI']);

		return $state;
	}
}

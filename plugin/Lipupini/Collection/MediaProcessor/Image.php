<?php

namespace Plugin\Lipupini\Collection\MediaProcessor;

use Imagine;
use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class Image extends Plugin {
	public function start(State $state): State {
		$extMimes = [
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
		];

		if (!preg_match('#^/c/file/([^/]+)/(small|large)/(.+(' . implode('|', array_keys($extMimes)) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return $state;
		}

		// If the URL has matched, we're going to shutdown after this plugin returns no matter what
		$state->lipupiniMethod = 'shutdown';

		$collectionFolderName = $matches[1];
		$sizePreset = $matches[2];
		$imagePath = $matches[3];
		$extension = $matches[4];

		Lipupini::validateCollectionFolderName($collectionFolderName);

		$pathOriginal = DIR_COLLECTION . '/' . $collectionFolderName . '/' . $imagePath;

		if (!file_exists($pathOriginal)) {
			http_response_code(404);
			echo 'Not found';
			return $state;
		}

		// Try all possible graphics drivers for Imagine
		try {
			$imagine = new Imagine\Imagick\Imagine();
		} catch (\Exception $e) {
			try {
				$imagine = new Imagine\Gmagick\Imagine();
			} catch (\Exception $e) {
				try {
					$imagine = new Imagine\Gd\Imagine();
				} catch (\Exception $e) {
					throw new Exception('Could not find a graphics library to process images');
				}
			}
		}

		mkdir(DIR_WEBROOT . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME), 0755, true);

		switch ($sizePreset) {
			case 'small' :
				$size = new Imagine\Image\Box(500, 1000);
				$mode = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
				$imagine->open($pathOriginal)
					->thumbnail($size, $mode)
					->save(DIR_WEBROOT . $_SERVER['REQUEST_URI'])
				;
				break;
			case 'large' :
				copy($pathOriginal, DIR_WEBROOT . $_SERVER['REQUEST_URI']);
				break;
			default :
				throw new Exception('Unknown size preset');
		}

		header('Content-type: ' . $extMimes[$extension]);
		readfile(DIR_WEBROOT . $_SERVER['REQUEST_URI']);

		return $state;
	}
}

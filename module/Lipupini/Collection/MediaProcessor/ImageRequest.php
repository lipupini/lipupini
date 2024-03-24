<?php

namespace Module\Lipupini\Collection\MediaProcessor;

ini_set('max_execution_time', 30);
ini_set('memory_limit', '512M');

use Imagine;
use Module\Lipupini\Collection;

class ImageRequest extends MediaProcessorRequest {
	public static function mimeTypes(): array {
		return [
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif',
		];
	}

	public function initialize(): void {
		if (!preg_match('#^/c/file/([^/]+)/image/(small|large)/(.+\.(' . implode('|', array_keys(self::mimeTypes())) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this module returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$sizePreset = $matches[2];
		$imagePath = rawurldecode($matches[3]);
		$extension = $matches[4];

		// We can use the same function that `Module\Lipupini\Collection\Request` uses
		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . '/' . $imagePath;

		if (!file_exists($pathOriginal)) {
			http_response_code(404);
			echo 'Not found';
			return;
		}

		// Try all possible graphics drivers for Imagine
		try {
			$imagine = new Imagine\Gd\Imagine();
		} catch (\Exception $e) {
			try {
				$imagine = new Imagine\Gmagick\Imagine();
			} catch (\Exception $e) {
				try {
					$imagine = new Imagine\Imagick\Imagine();
				} catch (\Exception $e) {
					throw new Exception('Could not find a graphics library to process images');
				}
			}
		}

		if (!is_dir($this->system->dirWebroot . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME))) {
			mkdir($this->system->dirWebroot . pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME), 0755, true);
		}

		switch ($sizePreset) {
			case 'small' :
				if (!file_exists($this->system->dirWebroot . $_SERVER['REQUEST_URI'])) {
					$size = new Imagine\Image\Box($this->system->mediaSizes[$sizePreset][0], $this->system->mediaSizes[$sizePreset][1]);
					$mode = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
					$imagine->open($pathOriginal)
						->thumbnail($size, $mode)
						->save($this->system->dirWebroot . $_SERVER['REQUEST_URI'])
					;
				}
				break;
			case 'large' :
				if (!file_exists($pathOriginal)) {
					symlink($pathOriginal, $this->system->dirWebroot . $_SERVER['REQUEST_URI']);
				}
				break;
			default :
				throw new Exception('Unknown size preset');
		}

		header('Content-type: ' . self::mimeTypes()[$extension]);
		// With the possibility of very large files and potential issues with static file serving, we are not using the `$this->system->responseContent` option here
		readfile($pathOriginal);
		exit();
	}
}

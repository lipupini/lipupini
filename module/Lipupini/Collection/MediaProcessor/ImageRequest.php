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
		if (!preg_match('#^/c/([^/]+)/image/(small|large)/(.+\.(' . implode('|', array_keys(self::mimeTypes())) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
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
						->strip()
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

	// https://www.php.net/manual/en/function.imagecreatefromgif.php#119564
	public static function isAnimatedGif(string $filename) {
		$fh = fopen($filename, 'rb');

		if (!$fh) {
			return false;
		}

		$totalCount = 0;
		$chunk = '';

		// An animated gif contains multiple "frames", with each frame having a header made up of:
		// * a static 4-byte sequence (\x00\x21\xF9\x04)
		// * 4 variable bytes
		// * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)

		// We read through the file until we reach the end of it, or we've found at least 2 frame headers.
		while (!feof($fh) && $totalCount < 2) {
			// Read 100kb at a time and append it to the remaining chunk.
			$chunk .= fread($fh, 1024 * 100);
			$count = preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
			$totalCount += $count;

			// Execute this block only if we found at least one match,
			// and if we did not reach the maximum number of matches needed.
			if ($count > 0 && $totalCount < 2) {
				// Get the last full expression match.
				$lastMatch = end($matches[0]);
				// Get the string after the last match.
				$end = strrpos($chunk, $lastMatch) + strlen($lastMatch);
				$chunk = substr($chunk, $end);
			}
		}

		fclose($fh);

		return $totalCount > 1;
	}
}

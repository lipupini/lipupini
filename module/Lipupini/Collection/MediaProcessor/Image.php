<?php

namespace Module\Lipupini\Collection\MediaProcessor;

ini_set('max_execution_time', 30);
ini_set('memory_limit', '512M');

use Imagine;
use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class Image {
	private static ?Imagine\Image\AbstractImagine $imagine = null;

	public static function mimeTypes(): array {
		return [
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif',
		];
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

	private static function imagine(): Imagine\Image\AbstractImagine {
		if (!is_null(static::$imagine)) {
			return static::$imagine;
		}

		// Try all possible graphics drivers for Imagine
		try {
			static::$imagine = new Imagine\Gd\Imagine();
		} catch (\Exception $e) {
			try {
				static::$imagine = new Imagine\Gmagick\Imagine();
			} catch (\Exception $e) {
				try {
					static::$imagine = new Imagine\Imagick\Imagine();
				} catch (\Exception $e) {
					throw new Exception('Could not find a graphics library to process images');
				}
			}
		}

		return static::$imagine;
	}

	public static function processAndCache(State $systemState, string $collectionFolderName, string $fileTypeFolder, string $sizePreset, string $filePath, bool $echoStatus = false): void {
		$cache = new Cache($systemState, $collectionFolderName);
		$collectionPath = $systemState->dirCollection . '/' . $collectionFolderName;

		$cache::webrootCacheSymlink($systemState, $collectionFolderName, $echoStatus);

		$fileCachePath = $cache->path() . '/' . $fileTypeFolder . '/' . $sizePreset . '/' . $filePath;

		if (file_exists($fileCachePath)) {
			return;
		}

		if ($echoStatus) {
			echo 'Creating ' . $sizePreset . ' cache file for `' . $filePath . '`...' . "\n";
		}

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		if (pathinfo($filePath, PATHINFO_EXTENSION) === 'gif') {
			if (static::isAnimatedGif($collectionPath . '/' . $filePath)) {
				if ($echoStatus) {
					echo 'Animated .gif detected, creating symlink to original for ' . $filePath . '...' . "\n";
				}
				symlink($collectionPath . '/' . $filePath, $fileCachePath);
				return;
			}
		}

		// Start with autorotating image based on EXIF data
		$autoRotate = new Imagine\Filter\Basic\Autorotate();
		$autoRotate->apply(static::imagine()->open($collectionPath . '/' . $filePath))
			// Strip all EXIF data
			->strip()
			// Resize
			->thumbnail(
				new Imagine\Image\Box($systemState->mediaSizes[$sizePreset][0],
					$systemState->mediaSizes[$sizePreset][1]), Imagine\Image\ImageInterface::THUMBNAIL_INSET)
			->save($fileCachePath)
		;
	}
}

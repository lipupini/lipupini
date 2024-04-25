<?php

namespace Module\Lipupini\Collection\MediaProcessor;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

use Imagine;
use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class Image {
	private static ?Imagine\Image\AbstractImagine $imagine = null;

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
			$count = preg_match_all('#\x00\x21\xF9\x04.{4}\x00[\x2C|\x21]#s', $chunk, $matches);
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

	public static function imagine(): Imagine\Image\AbstractImagine {
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

	public static function processAndCache(State $systemState, string $collectionName, string $fileTypeFolder, string $sizePreset, string $filePath, bool $echoStatus = false): false|string {
		$cache = new Cache($systemState, $collectionName);
		$collectionPath = $systemState->dirCollection . '/' . $collectionName;

		$cache::staticCacheSymlink($systemState, $collectionName);

		$fileCachePath = $cache->path() . '/' . $fileTypeFolder . '/' . $sizePreset . '/' . $filePath;

		// Make sure the files exists in the collection before proceeding
		if (!file_exists($collectionPath . '/' . $filePath)) {
			return false;
		}

		if (file_exists($fileCachePath)) {
			if (
				// If the cache path is a symlink, then we don't care about it because any changes to the linked file will work automatically
				!is_link($fileCachePath) &&
				// If the original file has been modified since the cache file was created
				filemtime($collectionPath . '/' . $filePath) > filemtime($fileCachePath))
			{
				if ($echoStatus) {
					echo 'Image preset ' . $sizePreset . ' for `' . $filePath . '` is outdated, deleting cache file...' . "\n";
				}
				// Delete the cache file
				unlink($fileCachePath);
			} else {
				// Otherwise, the cache file is current and we can serve it
				return $fileCachePath;
			}
		} else {
			$fileCacheDir = pathinfo($fileCachePath, PATHINFO_DIRNAME);
			if (!is_dir($fileCacheDir)) {
				mkdir($fileCacheDir, 0755, true);
			}
		}

		if ($echoStatus) {
			echo 'Creating ' . $sizePreset . ' cache file for `' . $filePath . '`...' . "\n";
		}

		// In the collection's `.lipupini` folder if there is a subfolder with the same name as the `$sizePreset`
		// and an image exists in there with the same name, symlink that instead of processing the size automatically.
		// This makes sure that work on custom thumbnails is not lost.
		$customImagePath = $collectionPath . '/.lipupini/image/' . $sizePreset . '/' . $filePath;
		// If the custom image exists, a cache file for the custom image should be a symlink so check that it is correct
		if (file_exists($customImagePath) && (!is_link($fileCachePath) || readlink($fileCachePath) !== $customImagePath)) {
			if ($echoStatus) {
				echo 'Found a custom image for size preset `' . $sizePreset . '`: `' . $filePath . '`....' . "\n";
			}
			$cache::createSymlink($customImagePath, $fileCachePath);
			return $fileCachePath;
		}

		if (pathinfo($filePath, PATHINFO_EXTENSION) === 'gif') {
			if (static::isAnimatedGif($collectionPath . '/' . $filePath)) {
				// Check whether the animated .gif symlink is already there, if so return it
				if (file_exists($fileCachePath)) {
					return $fileCachePath;
				}
				if ($echoStatus) {
					echo 'Animated .gif detected, creating symlink to original for ' . $filePath . '...' . "\n";
				}
				$cache::createSymlink($collectionPath . '/' . $filePath, $fileCachePath);
				return $fileCachePath;
			}
		}

		// We're not using a custom image at this point, so that cache path should be a file not a link
		// Here we are checking if the cache file is already there. If it was invalid it should have been deleted above.
		if (file_exists($fileCachePath) && !is_link($fileCachePath)) {
			return $fileCachePath;
		}

		// At this point we can perform the image processing
		// Start with autorotating image based on EXIF data
		$autoRotate = new Imagine\Filter\Basic\Autorotate();
		$autoRotate->apply(static::imagine()->open($collectionPath . '/' . $filePath))
			// Strip all EXIF data
			->strip()
			// Resize
			->thumbnail(
				new Imagine\Image\Box(
					$systemState->mediaSize[$sizePreset][0],
					$systemState->mediaSize[$sizePreset][1]
				), Imagine\Image\ImageInterface::THUMBNAIL_INSET)
			->save($fileCachePath, $systemState->imageQuality)
		;

		return $fileCachePath;
	}
}

<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Imagine;
use Module\Lipupini\Collection\Cache;
use Module\Lipupini\Collection\Utility;
use Module\Lipupini\State;

class VideoThumbnail {
	public static function cacheSymlinkVideoThumbnail(State $systemState, string $collectionName, string $videoPath, bool $echoStatus = false): false|string {
		// Make sure the file exists in the collection before proceeding
		if (!file_exists($systemState->dirCollection . '/' . $collectionName . '/' . $videoPath)) {
			return false;
		}

		$thumbnailPath = $videoPath . '.jpg';
		$thumbnailPathFull = $systemState->dirCollection . '/' . $collectionName . '/.lipupini/video/thumbnail/' . $thumbnailPath;

		// Try to create the video thumbnail if it isn't already there
		if (!file_exists($thumbnailPathFull)) {
			$result = static::saveMiddleFramePng($systemState, $collectionName, $videoPath, $thumbnailPathFull, $echoStatus);
			if (!$result || !file_exists($thumbnailPathFull)) return false;
		}

		$cache = new Cache($systemState, $collectionName);
		$fileCachePath = $cache->path() . '/video/thumbnail/' . $thumbnailPath;

		// If `$fileCachePath` is already there we don't need to do a cache symlink it so return
		if (file_exists($fileCachePath)) {
			if (is_link($fileCachePath)) {
				return $fileCachePath;
			}
			// If it's not a symlink, let's delete what's there and make it a symlink
			unlink($fileCachePath);
		// If the cache file doesn't exist, make sure the directory does before creating the cache file
		} else {
			$fileCacheDir = pathinfo($fileCachePath, PATHINFO_DIRNAME);
			if (!is_dir($fileCacheDir)) {
				mkdir($fileCacheDir, 0755, true);
			}
		}

		if ($echoStatus) {
			echo 'Symlinking video thumbnail to cache for `' . $thumbnailPath . '`...' . "\n";
		}

		// Link the thumbnail path to the collection's cache
		$cache::createSymlink(
			$thumbnailPathFull,
			$fileCachePath
		);

		// Create the collection's cache link in `webroot` if it does not exist
		$cache::staticCacheSymlink($systemState, $collectionName);

		return $fileCachePath;
	}

	public static function saveMiddleFramePng(State $systemState, string $collectionName, string $videoPath, string $thumbnailPathFull, bool $echoStatus = false) {
		if (!(new Utility($systemState))->hasFfmpeg()) {
			return false;
		}

		if ($echoStatus) {
			echo 'Saving video thumbnail for `' . $videoPath . '`...' . "\n";
		}

		if (!is_dir(pathinfo($thumbnailPathFull, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($thumbnailPathFull, PATHINFO_DIRNAME), 0755, true);
		}

		$command = $systemState->dirRoot . '/bin/ffmpeg-video-thumbnail.php '
			. escapeshellarg($systemState->dirCollection . '/' . $collectionName . '/' . $videoPath)
			. ' ' . escapeshellarg($thumbnailPathFull);
		// `ffmpeg` output is purged from display with `> /dev/null 2>&1`. Remove it to see `ffmpeg` output in webserver logs
		$command .= ' > /dev/null 2>&1';
		exec($command, $output, $returnCode);

		if ($returnCode !== 0) {
			if ($echoStatus) {
				echo 'ERROR: Received non-zero exit status from `ffmpeg` for ' . $videoPath . "\n";
			} else {
				error_log('ERROR: Received non-zero exit status from `ffmpeg` for ' . $videoPath);
			}
			return false;
		}

		// Resize the generated thumbnail (should overwrite)
		Image::imagine()->open($thumbnailPathFull)
			// Strip all EXIF data
			->strip()
			// Resize
			->thumbnail(
				new Imagine\Image\Box(
					$systemState->mediaSize['thumbnail'][0],
					$systemState->mediaSize['thumbnail'][1]
				), Imagine\Image\ImageInterface::THUMBNAIL_INSET)
			->save($thumbnailPathFull, $systemState->imageQuality);

		return true;
	}
}

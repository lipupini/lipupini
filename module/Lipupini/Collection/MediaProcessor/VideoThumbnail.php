<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Imagine;
use Module\Lipupini\Collection\Cache;
use Module\Lipupini\Collection\Utility;
use Module\Lipupini\State;

class VideoThumbnail {
	public static function cacheSymlinkVideoThumbnail(State $systemState, string $collectionName, string $videoPath, bool $echoStatus = false): false|string {
		$cache = new Cache($systemState, $collectionName);
		$thumbnailPath = $videoPath . '.png';

		$thumbnailPathFull = $systemState->dirCollection . '/' . $collectionName . '/.lipupini/video/thumbnail/' . $thumbnailPath;
		$fileCachePath = $cache->path() . '/video/thumbnail/' . $thumbnailPath;

		$cache::staticCacheSymlink($systemState, $collectionName);

		// One tradeoff with doing this first is that the file can be deleted from the collection's `thumbnail` folder but still show if it stays in `cache`
		// The benefit is that it won't try to use `ffmpeg` and grab the frame if it hasn't yet, so it's potentially faster to check this way
		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		}

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		// If the screenshot already exists then don't try to create it
		if (!file_exists($thumbnailPathFull)) {
			static::saveMiddleFramePng($systemState, $collectionName, $videoPath, $thumbnailPath, $echoStatus);

			// After grabbing the middle frame, `$thumbnailPathFull` should exist
			if (!file_exists($thumbnailPathFull)) {
				return false;
			}
		}

		// If `$fileCachePath` is already there we don't need to do a cache symlink it so return
		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		}

		if ($echoStatus) {
			echo 'Symlinking video thumbnail to cache for `' . $thumbnailPath . '`...' . "\n";
		}

		// Link the thumbnail path to the collection's cache
		$cache::createSymlink(
			$thumbnailPathFull,
			$fileCachePath
		);

		return $fileCachePath;
	}

	public static function saveMiddleFramePng(State $systemState, string $collectionName, string $videoPath, string $thumbnailPath, bool $echoStatus = false) {
		if (!Utility::hasFfmpeg($systemState)) {
			return false;
		}

		$collectionPath = $systemState->dirCollection . '/' . $collectionName;
		$thumbnailPathFull = $systemState->dirCollection . '/' . $collectionName . '/.lipupini/video/thumbnail/' . $thumbnailPath;

		if (file_exists($thumbnailPathFull)) {
			return true;
		}

		if (!is_dir(pathinfo($thumbnailPathFull, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($thumbnailPathFull, PATHINFO_DIRNAME), 0755, true);
		}

		if ($echoStatus) {
			echo 'Saving video thumbnail for `' . $videoPath . '`...' . "\n";
		}

		$command = $systemState->dirRoot . '/bin/ffmpeg-video-thumbnail.php ' . escapeshellarg($collectionPath . '/' . $videoPath) . ' ' . escapeshellarg($thumbnailPathFull) . ' > /dev/null 2>&1';
		// `ffmpeg` output is purged from display with `> /dev/null 2>&1`. Remove it to see `ffmpeg` output
		exec($command, $output, $returnCode);

		if ($returnCode !== 0) {
			if ($echoStatus) {
				echo 'ERROR: Received non-zero exit status from `ffmpeg` for ' . $videoPath . "\n";
			}
			return false;
		}

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

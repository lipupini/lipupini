<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Imagine;
use Module\Lipupini\Collection\Cache;
use Module\Lipupini\Collection\Utility;
use Module\Lipupini\State;

class AudioWaveform {
	public static function cacheSymlinkAudioWaveform(State $systemState, string $collectionFolderName, string $audioPath, bool $echoStatus = false): false|string {
		$cache = new Cache($systemState, $collectionFolderName);
		$waveformPath = $audioPath . '.waveform.png';

		$waveformPathFull = $systemState->dirCollection . '/' . $collectionFolderName . '/.lipupini/thumbnail/' . $waveformPath;
		$fileCachePath = $cache->path() . '/thumbnail/' . $waveformPath;

		$cache::staticCacheSymlink($systemState, $collectionFolderName);

		// One tradeoff with doing this first is that the file can be deleted from the collection's `thumbnail` folder but still show if it stays in `cache`
		// The benefit is that it won't try to use `ffmpeg` and grab the frame if it hasn't yet, so it's potentially faster to check this way
		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		}

		// Make sure the file exists in the collection before proceeding
		if (!file_exists($systemState->dirCollection . '/' . $collectionFolderName . '/' . $audioPath)) {
			return false;
		}

		if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
		}

		// If the waveform already exists then don't try to create it
		if (!file_exists($waveformPathFull)) {
			static::saveAudioWaveform($systemState, $collectionFolderName, $audioPath, $waveformPath, $echoStatus);

			// After generating the waveform, `$waveformPathFull` should exist
			if (!file_exists($waveformPathFull)) {
				return false;
			}
		}

		// If `$fileCachePath` is already there we don't need to do a cache symlink, and we can use what's there
		if (file_exists($fileCachePath)) {
			return $fileCachePath;
		}

		if ($echoStatus) {
			echo 'Symlinking audio waveform to cache for `' . $waveformPath . '`...' . "\n";
		}

		// Link the thumbnail path to the collection's cache
		$cache::createSymlink(
			$waveformPathFull,
			$fileCachePath
		);

		return $fileCachePath;
	}

	public static function saveAudioWaveform(State $systemState, string $collectionFolderName, string $audioPath, string $waveformPath, bool $echoStatus = false) {
		if (!Utility::hasFfmpeg($systemState)) {
			return false;
		}

		$collectionPath = $systemState->dirCollection . '/' . $collectionFolderName;
		$waveformPathFull = $systemState->dirCollection . '/' . $collectionFolderName . '/.lipupini/thumbnail/' . $waveformPath;

		if (file_exists($waveformPathFull)) {
			return true;
		}

		if (!is_dir(pathinfo($waveformPathFull, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($waveformPathFull, PATHINFO_DIRNAME), 0755, true);
		}

		if ($echoStatus) {
			echo 'Saving audio waveform for `' . $audioPath . '`...' . "\n";
		}

		$command = $systemState->dirRoot . '/bin/ffmpeg-audio-waveform.php ' . escapeshellarg($collectionPath . '/' . $audioPath) . ' ' . escapeshellarg($waveformPathFull);
		// `ffmpeg` output is purged from display with `> /dev/null 2>&1`. Remove it to see `ffmpeg` output
		$command .=  ' > /dev/null 2>&1';
		exec($command, $output, $returnCode);

		if ($returnCode !== 0) {
			if ($echoStatus) {
				echo 'ERROR: Received non-zero exit status from `ffmpeg` for ' . $audioPath . "\n";
			}
			return false;
		}

		return true;
	}
}

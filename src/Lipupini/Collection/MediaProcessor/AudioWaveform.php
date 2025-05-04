<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Imagine;
use Module\Lipupini\Collection\Cache;
use Module\Lipupini\Collection\Utility;
use Module\Lipupini\State;

class AudioWaveform {
	public static function cacheSymlinkAudioWaveform(State $systemState, string $collectionName, string $audioPath, bool $echoStatus = false): false|string {
		// Make sure the file exists in the collection before proceeding
		if (!file_exists($systemState->dirCollection . '/' . $collectionName . '/' . $audioPath)) {
			return false;
		}

		$waveformPath = $audioPath . '.png';
		$waveformPathFull = $systemState->dirCollection . '/' . $collectionName . '/.lipupini/audio/waveform/' . $waveformPath;

		// Try to create the waveform if it isn't already there
		if (!file_exists($waveformPathFull)) {
			$result = static::saveAudioWaveform($systemState, $collectionName, $audioPath, $waveformPathFull, $echoStatus);
			if (!$result || !file_exists($waveformPathFull)) return false;
		}

		$cache = new Cache($systemState, $collectionName);
		$fileCachePath = $cache->path() . '/audio/waveform/' . $waveformPath;

		// If `$fileCachePath` is already there we don't need to do a cache symlink, and we can use what's there
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
			echo 'Symlinking audio waveform to cache for `' . $waveformPath . '`...' . "\n";
		}

		// Link the waveform path to the collection's cache
		$cache::createSymlink(
			$waveformPathFull,
			$fileCachePath
		);

		// Create the collection's cache link in `webroot` if it does not exist
		$cache::staticCacheSymlink($systemState, $collectionName);

		return $fileCachePath;
	}

	public static function saveAudioWaveform(State $systemState, string $collectionName, string $audioPath, string $waveformPathFull, bool $echoStatus = false) {
		if (!(new Utility($systemState))->hasFfmpeg()) {
			return false;
		}

		if (!is_dir(pathinfo($waveformPathFull, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($waveformPathFull, PATHINFO_DIRNAME), 0755, true);
		}

		if ($echoStatus) {
			echo 'Saving audio waveform for `' . $audioPath . '`...' . "\n";
		}

		$command = $systemState->dirRoot . '/bin/ffmpeg-audio-waveform.php '
			. escapeshellarg($systemState->dirCollection . '/' . $collectionName . '/' . $audioPath)
			. ' ' . escapeshellarg($waveformPathFull);
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

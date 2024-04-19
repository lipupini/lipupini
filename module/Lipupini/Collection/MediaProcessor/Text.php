<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class Text {
	public static function processAndCache(State $systemState, string $collectionName, string $fileTypeFolder, string $filePath, bool $echoStatus = false): string {
		$collectionPath = $systemState->dirCollection . '/' . $collectionName;

		// Make sure the file exists in the collection before proceeding
		if (!file_exists($collectionPath . '/' . $filePath)) {
			return false;
		}

		$cache = new Cache($systemState, $collectionName);
		$fileCachePathMd = $cache->path() . '/' . $fileTypeFolder . '/markdown/' . $filePath;

		if (!file_exists($fileCachePathMd)) {
			$fileCacheDirMd = pathinfo($fileCachePathMd, PATHINFO_DIRNAME);
			if (!is_dir($fileCacheDirMd)) {
				mkdir($fileCacheDirMd, 0755, true);
			}

			if ($echoStatus) {
				echo 'Symlinking Markdown cache file for `' . $filePath . '`...' . "\n";
			}
			$cache::createSymlink($collectionPath . '/' . $filePath, $fileCachePathMd);
		}

		$fileCachePathHtml = $cache->path() . '/' . $fileTypeFolder . '/html/' . $filePath . '.html';

		if (file_exists($fileCachePathHtml)) {
			if (filemtime($collectionPath . '/' . $filePath) < filemtime($fileCachePathHtml)) {
				return $fileCachePathHtml;
			}
			if ($echoStatus) {
				echo 'Deleting outdated cache file for `' . $filePath . '`...' . "\n";
			}
			unlink($fileCachePathHtml);
		} else {
			$fileCacheDirHtml = pathinfo($fileCachePathHtml, PATHINFO_DIRNAME);
			if (!is_dir($fileCacheDirHtml)) {
				mkdir($fileCacheDirHtml, 0755, true);
			}
		}

		if ($echoStatus) {
			echo 'Generating HTML cache files for `' . $filePath . '`...' . "\n";
		}

		try {
			$rendered = Parsedown::instance()->text(file_get_contents($collectionPath . '/' . $filePath));
		} catch (\Exception $e) {
			throw new Exception('Could not render markdown file');
		}

		$rendered = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>' . "\n"
			. $rendered . "\n"
			. '</body></html>' . "\n";

		file_put_contents($fileCachePathHtml, $rendered);

		// Create the collection's cache link in `webroot` if it does not exist
		$cache::staticCacheSymlink($systemState, $collectionName);

		return $fileCachePathHtml;
	}
}

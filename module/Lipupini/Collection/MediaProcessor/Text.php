<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class Text {
	public static function processAndCache(State $systemState, string $collectionName, string $fileTypeFolder, string $filePath, bool $echoStatus = false): string {
		$cache = new Cache($systemState, $collectionName);
		$fileCachePathMd = $cache->path() . '/' . $fileTypeFolder . '/markdown/' . $filePath;
		$collectionPath = $systemState->dirCollection . '/' . $collectionName;

		$cache::staticCacheSymlink($systemState, $collectionName);

		if (!is_dir(pathinfo($fileCachePathMd, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePathMd, PATHINFO_DIRNAME), 0755, true);
		}

		if (!file_exists($fileCachePathMd)) {
			if ($echoStatus) {
				echo 'Symlinking Markdown cache file for `' . $filePath . '`...' . "\n";
			}
			$cache::createSymlink($collectionPath . '/' . $filePath, $fileCachePathMd);
		}

		$fileCachePathHtml = $cache->path() . '/' . $fileTypeFolder . '/html/' . $filePath . '.html';

		if (!is_dir(pathinfo($fileCachePathHtml, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePathHtml, PATHINFO_DIRNAME), 0755, true);
		}

		if (file_exists($fileCachePathHtml)) {
			if (filemtime($collectionPath . '/' . $filePath) < filemtime($fileCachePathHtml)) {
				return $fileCachePathHtml;
			}
			if ($echoStatus) {
				echo 'Deleting outdated cache file for `' . $filePath . '`...' . "\n";
			}
			unlink($fileCachePathHtml);
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

		return $fileCachePathHtml;
	}
}

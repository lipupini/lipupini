<?php

namespace Module\Lipupini\Collection\MediaProcessor;

use Module\Lipupini\Collection\Cache;
use Module\Lipupini\State;

class Text {
	public static function processAndCache(State $systemState, string $collectionFolderName, string $fileTypeFolder, string $filePath, bool $echoStatus = false): bool {
		$cache = new Cache($systemState, $collectionFolderName);
		$fileCachePathMd = $cache->path() . '/' . $fileTypeFolder . '/' . $filePath;
		$collectionPath = $systemState->dirCollection . '/' . $collectionFolderName;

		$cache::webrootCacheSymlink($systemState, $collectionFolderName, $echoStatus);

		if (!is_dir(pathinfo($fileCachePathMd, PATHINFO_DIRNAME))) {
			mkdir(pathinfo($fileCachePathMd, PATHINFO_DIRNAME), 0755, true);

			if (!file_exists($fileCachePathMd)) {
				if ($echoStatus) {
					echo 'Symlinking Markdown cache files for `' . $filePath . '`...' . "\n";
				}
				$cache::createSymlink($collectionPath . '/' . $filePath, $fileCachePathMd);
			}
		}

		$fileCachePathHtml = $cache->path() . '/' . $fileTypeFolder . '/' . $filePath . '.html';

		if (file_exists($fileCachePathHtml)) {
			return true;
		}

		if ($echoStatus) {
			echo 'Generating HTML cache files for `' . $filePath . '`...' . "\n";
		}

		try {
			$rendered = Parsedown::instance()->text(file_get_contents($systemState->dirCollection . '/' . $collectionFolderName . '/' . $filePath));
		} catch (\Exception $e) {
			throw new Exception('Could not render markdown file');
		}

		$rendered = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>' . "\n"
			. $rendered . "\n"
			. '</body></html>' . "\n";

		return !!file_put_contents($fileCachePathHtml, $rendered);
	}
}

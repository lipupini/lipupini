#!/usr/bin/env php
<?php

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor;
use Module\Lipupini\Collection\MediaProcessor\Exception;
use Module\Lipupini\State;

/** @var State $systemState */
$systemState = require(__DIR__ . '/../config/system.php');

if (empty($argv[1])) {
	$confirm = 'Y'; // readline('No collection folder specified. Do you want to process all collections? [Y/n] ');
	if (strtoupper($confirm) !== 'Y') {
		exit(0);
	}
	foreach (new \DirectoryIterator($systemState->dirCollection) as $item) {
		if ($item->getFilename()[0] === '.' || !$item->isDir()) {
			continue;
		}
		echo "\n" . 'Processing collection folder `' . $item->getFilename() . '`...' . "\n";
		passthru(__FILE__ . ' ' . $item->getFilename());
	}
	echo "\n" . 'Done' . "\n";
	exit(0);
}

$collectionFolder = $argv[1];

$collectionUtility = new Collection\Utility($systemState);
$collectionUtility->validateCollectionFolderName($collectionFolder);

$collectionPath = $systemState->dirCollection . '/' . $collectionFolder;
$lipupiniPath = $collectionPath . '/.lipupini';

$collectionData = $collectionUtility->getCollectionDataRecursive($collectionFolder);
$collectionCache = new Collection\Cache($systemState, $collectionFolder);

$fileTypes = [
		'video' => MediaProcessor\VideoRequest::mimeTypes(),
		'audio' => MediaProcessor\AudioRequest::mimeTypes(),
		'image' => MediaProcessor\ImageRequest::mimeTypes(),
		'text' => MediaProcessor\TextRequest::mimeTypes(),
];

$collectionDataPrepared = [];

foreach (array_keys($collectionData) as $filePath) {
	$extension = pathinfo($filePath, PATHINFO_EXTENSION);
	foreach ($fileTypes as $type => $mime) {
		if (array_key_exists($extension, $mime)) {
			$collectionDataPrepared[$type][] = $filePath;
		}
	}
}

$cachePath = $collectionCache->path();
$cacheDataPrepared = [];

foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cachePath)) as $filePath => $fileInfo) {
	if ($fileInfo->getFilename()[0] === '.') {
		continue;
	}

	if (!preg_match('#^' . preg_quote($cachePath) . '/([^/]+)/#', $filePath, $matches)) {
		echo 'Unexpected cache file path: ' . $filePath . "\n";
		continue;
	}

	$fileType = $matches[1];
	$filePathPrepared = preg_replace('#^' . preg_quote($matches[0]) . '#', '', $filePath);
	$cacheDataPrepared[$fileType][] = $filePathPrepared;
}

if (!empty($cacheDataPrepared['image'])) {
	$cacheDataPreparedImage = $cacheDataPrepared['image'];
	unset($cacheDataPrepared['image']);

	foreach ($cacheDataPreparedImage as $image) {
		if (!preg_match('#^([^/]+)/#', $image, $matches)) {
			echo 'Unexpected image size value: ' . $image . "\n";
			continue;
		}

		$imageSize = $matches[1];
		$cacheDataPrepared['image'][$imageSize][] = preg_replace('#^' . $imageSize . '/#', '', $image);
	}
}

// START: Delete cache data that doesn't exist in collection
foreach ($cacheDataPrepared as $fileType => $filePaths) {
	if ($fileType === 'image') {
		foreach ($filePaths as $imageSize => $imageFilePaths) {
			foreach ($imageFilePaths as $imageFilePath) {
				if (!file_exists($collectionPath . '/' . $imageFilePath)) {
					echo 'File does not exist in collection, deleting `' . $imageFilePath . '`...' . "\n";
					unlink($cachePath . '/' . $fileType . '/' . $imageSize . '/' . $imageFilePath);
				}
			}
		}
		continue;
	}

	// Images above are a special case, process everything else here
	foreach ($filePaths as $filePath) {
		if ($fileType === 'text') {
			$filePath = preg_replace('#\.html$#', '', $filePath);
		}
		if (!file_exists($collectionPath . '/' . $filePath)) {
			echo 'File does not exist in collection, deleting `' . $filePath . '`...' . "\n";
			unlink($cachePath . '/' . $fileType . '/' . $filePath);
		}
	}
}
// END: Delete cache data that doesn't exist in collection

// START: Process media cache
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

foreach ($collectionDataPrepared as $fileType => $filePaths) {
	switch ($fileType) {
		case 'image' :
			foreach ($filePaths as $filePath) {
				foreach ($systemState->mediaSizes as $imageSize => $dimensions) {
					$fileCachePath = $cachePath . '/' . $fileType . '/' . $imageSize . '/' . $filePath;

					if (file_exists($fileCachePath)) {
						continue;
					}

					echo 'Creating ' . $imageSize . ' cache file for `' . $filePath . '`...' . "\n";

					if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
						mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
					}

					if (pathinfo($filePath, PATHINFO_EXTENSION) === 'gif') {
						if (MediaProcessor\ImageRequest::isAnimatedGif($collectionPath . '/' . $filePath)) {
							echo 'Animated .gif detected, creating symlink to original for ' . $filePath . '...' . "\n";
							symlink($collectionPath . '/' . $filePath, $fileCachePath);
							continue;
						}
					}

					$size = new Imagine\Image\Box($dimensions[0], $dimensions[1]);
					$mode = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
					$imagine->open($collectionPath . '/' . $filePath)
						->strip()
						->thumbnail($size, $mode)
						->save($fileCachePath)
					;
				}
			}
			break;

		case 'audio' :
		case 'video' :
			foreach ($filePaths as $filePath) {
				$fileCachePath = $cachePath . '/' . $fileType . '/' . $filePath;

				if (file_exists($fileCachePath)) {
					continue;
				}

				if (!is_dir(pathinfo($fileCachePath, PATHINFO_DIRNAME))) {
					mkdir(pathinfo($fileCachePath, PATHINFO_DIRNAME), 0755, true);
				}

				symlink($collectionPath . '/' . $filePath, $fileCachePath);
			}
			break;

		case 'text' :
			foreach ($filePaths as $filePath) {
				$fileCachePathMd = $cachePath . '/' . $fileType . '/' . $filePath;

				if (!is_dir(pathinfo($fileCachePathMd, PATHINFO_DIRNAME))) {
					mkdir(pathinfo($fileCachePathMd, PATHINFO_DIRNAME), 0755, true);

					if (!file_exists($fileCachePathMd)) {
						symlink($collectionPath . '/' . $filePath, $fileCachePathMd);
					}
				}

				$fileCachePathHtml = $cachePath . '/' . $fileType . '/' . $filePath . '.html';

				if (file_exists($fileCachePathHtml)) {
					continue;
				}

				try {
					$rendered = Collection\MediaProcessor\Parsedown::instance()->text(file_get_contents($collectionPath . '/' . $filePath));
				} catch (\Exception $e) {
					throw new Exception('Could not render markdown file');
				}

				$rendered = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>' . "\n"
					. $rendered . "\n"
					. '</body></html>' . "\n";

				file_put_contents($fileCachePathHtml, $rendered);
			}
			break;
	}
}
// END: Process media cache

// START: Ensure that collection cache folder is symlinked to `webroot` cache (`c`) folder
$webrootCacheDir = $systemState->dirWebroot . '/c/' . $collectionFolder;
if (!is_dir($webrootCacheDir)) {
	echo 'Creating `webroot` static cache symlink at `' . $webrootCacheDir . '`...' . "\n";
	symlink($cachePath, $webrootCacheDir);
}
// END: Ensure that collection cache folder is symlinked to `webroot` cache (`c`) folder

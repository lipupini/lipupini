#!/usr/bin/env php
<?php

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor;
use Module\Lipupini\State;

/** @var State $systemState */
$systemState = require(__DIR__ . '/../config/system.php');

$useFfmpeg = commandExists('ffmpeg');

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

$collectionFolderName = $argv[1];

$collectionUtility = new Collection\Utility($systemState);
$collectionUtility->validateCollectionFolderName($collectionFolderName);

$collectionPath = $systemState->dirCollection . '/' . $collectionFolderName;
$lipupiniPath = $collectionPath . '/.lipupini';

$collectionData = $collectionUtility->getCollectionDataRecursive($collectionFolderName);
$collectionCache = new Collection\Cache($systemState, $collectionFolderName);

// START: Prepare collection data
$collectionDataPrepared = [];
foreach (array_keys($collectionData) as $filePath) {
	$extension = pathinfo($filePath, PATHINFO_EXTENSION);
	foreach (Collection\Cache::fileTypes() as $type => $mime) {
		if (array_key_exists($extension, $mime)) {
			$collectionDataPrepared[$type][] = $filePath;
		}
	}
}
// END: Prepare collection data

// START: Delete cache data that doesn't exist in collection
$collectionCache->cleanCacheDir($collectionPath);
// END: Delete cache data that doesn't exist in collection

// START: Process media cache
foreach ($collectionDataPrepared as $fileTypeFolder => $filePaths) {
	switch ($fileTypeFolder) {
		case 'image' :
			foreach ($filePaths as $filePath) {
				foreach ($systemState->mediaSizes as $imageSize => $dimensions) {
					MediaProcessor\Image::processAndCache($systemState, $collectionFolderName, $fileTypeFolder, $imageSize, $filePath, echoStatus: true);
				}
			}
			break;
		case 'audio' :
			foreach ($filePaths as $filePath) {
				MediaProcessor\Audio::cacheSymlink($systemState, $collectionFolderName, $fileTypeFolder, $filePath, echoStatus: true);
			}
			break;
		case 'video' :
			foreach ($filePaths as $filePath) {
				MediaProcessor\Video::cacheSymlink($systemState, $collectionFolderName, $fileTypeFolder, $filePath, echoStatus: true);

				$postPath = $lipupiniPath . '/video-poster/' . $filePath . '.png';

				if (file_exists($postPath) || !$useFfmpeg) {
					continue;
				}

				if (!is_dir(pathinfo($postPath, PATHINFO_DIRNAME))) {
					mkdir(pathinfo($postPath, PATHINFO_DIRNAME), 0755, true);
				}

				echo 'Saving video poster for `' . $filePath . '`... ';
				exec(__DIR__ . '/ffmpeg-video-poster.php ' . escapeshellarg($collectionPath . '/' . $filePath) . ' ' . escapeshellarg($postPath) . ' > /dev/null 2>&1', $output, $returnCode);
				echo ($returnCode !== 0 ? 'ERROR' : '') . "\n";

				MediaProcessor\VideoPoster::cacheSymlinkVideoPoster($systemState, $collectionFolderName, $filePath . '.png', true);
			}
			break;
		case 'text' :
			foreach ($filePaths as $filePath) {
				MediaProcessor\Text::processAndCache($systemState, $collectionFolderName, $fileTypeFolder, $filePath, echoStatus: true);
			}
			break;
	}
}
// END: Process media cache

// https://beamtic.com/if-command-exists-php
function commandExists($command_name) {
	$test_method = (false === stripos(PHP_OS, 'win')) ? 'command -v' : 'where';
	return (null === shell_exec("$test_method $command_name")) ? false : true;
}

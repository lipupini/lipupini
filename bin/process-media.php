#!/usr/bin/env php
<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

use Module\Lipupini\Collection;
use Module\Lipupini\Collection\MediaProcessor;
use Module\Lipupini\State;

// See `readline` note in root README.md as this script might benefit from prompts

/** @var State $systemState */
$systemState = require(__DIR__ . '/../system/config/state.php');

if (empty($argv[1])) {
	$confirm = 'Y'; // readline('No collection folder specified. Do you want to process all collections? [Y/n] ');
	if (strtoupper($confirm) !== 'Y') {
		exit(0);
	}
	foreach ((new Collection\Utility($systemState))->allCollectionFolders() as $collectionFolder) {
		echo "\n" . 'Processing collection folder `' . $collectionFolder . '`...' . "\n";
		passthru(__FILE__ . ' ' . $collectionFolder);
	}
	echo "\n" . 'Done' . "\n";
	exit(0);
}

$collectionName = $argv[1];

$collectionUtility = new Collection\Utility($systemState);
$collectionUtility->validateCollectionName($collectionName);

$collectionPath = $systemState->dirCollection . '/' . $collectionName;
$lipupiniPath = $collectionPath . '/.lipupini';

$collectionData = $collectionUtility->getCollectionDataRecursive($collectionName);
$collectionCache = new Collection\Cache($systemState, $collectionName);

// START: Prepare collection data
$collectionDataPrepared = [];
foreach (array_keys($collectionData) as $filePath) {
	$extension = pathinfo($filePath, PATHINFO_EXTENSION);
	foreach ($systemState->mediaType as $type => $mime) {
		if (array_key_exists($extension, $mime)) {
			$collectionDataPrepared[$type][] = $filePath;
		}
	}
}
// END: Prepare collection data

// START: Delete cache data that doesn't exist in collection
$collectionCache->cleanCacheDir($systemState, $collectionName, true);
// END: Delete cache data that doesn't exist in collection

// START: Process media cache
foreach ($collectionDataPrepared as $fileTypeFolder => $filePaths) {
	switch ($fileTypeFolder) {
		case 'image' :
			foreach ($filePaths as $filePath) {
				foreach ($systemState->mediaSize as $imageSize => $dimensions) {
					MediaProcessor\Image::processAndCache($systemState, $collectionName, $fileTypeFolder, $imageSize, $filePath, echoStatus: true);
				}
			}
			break;
		case 'audio' :
			foreach ($filePaths as $filePath) {
				MediaProcessor\Audio::cacheSymlink($systemState, $collectionName, $fileTypeFolder, $filePath, echoStatus: true);
				MediaProcessor\AudioThumbnail::cacheSymlinkAudioThumbnail($systemState, $collectionName, $filePath, echoStatus: true);
				MediaProcessor\AudioWaveform::cacheSymlinkAudioWaveform($systemState, $collectionName, $filePath, echoStatus: true);
			}
			break;
		case 'video' :
			foreach ($filePaths as $filePath) {
				MediaProcessor\Video::cacheSymlink($systemState, $collectionName, $fileTypeFolder, $filePath, echoStatus: true);
				MediaProcessor\VideoThumbnail::cacheSymlinkVideoThumbnail($systemState, $collectionName, $filePath, echoStatus: true);
			}
			break;
		case 'text' :
			foreach ($filePaths as $filePath) {
				MediaProcessor\Text::processAndCache($systemState, $collectionName, $fileTypeFolder, $filePath, echoStatus: true);
			}
			break;
	}
}
// END: Process media cache

$defaultAvatarPath = $systemState->dirWebroot . MediaProcessor\Avatar::DEFAULT_IMAGE_PATH;
$defaultAvatarSha1 = sha1_file($defaultAvatarPath);

// BEGIN: Process avatar cache
$collectionFolderPath = $systemState->dirCollection . '/' . $collectionName;
$collectionAvatarPath = $collectionFolderPath . '/.lipupini/avatar.png';
$collectionCacheAvatarPath = $collectionFolderPath . '/.lipupini/cache/avatar.png';
$collectionCacheAvatarSha1 = file_exists($collectionCacheAvatarPath) ? sha1_file($collectionCacheAvatarPath) : null;

// If the default avatar is currently cached in a collection, but the avatar image has since been updated
if (
	$collectionCacheAvatarSha1 && file_exists($collectionAvatarPath) &&
	$defaultAvatarSha1 === $collectionCacheAvatarSha1
) {
	echo 'Collection avatar for `' . $collectionName . '` has been updated from the default image...' . "\n";
	unlink($collectionCacheAvatarPath);
}

MediaProcessor\Avatar::cacheSymlinkAvatar($systemState, $collectionName, $collectionAvatarPath, true);
// BEGIN: Process avatar cache

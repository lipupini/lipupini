#!/usr/bin/env php
<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

use Module\Lipupini\Collection;
use Module\Lipupini\State;

// See `readline` note in root README.md
$sleepFor = 10;

/** @var State $systemState */
$systemState = require(__DIR__ . '/../system/config/state.php');

echo 'WARNING: This script will overwrite original collection JPEG files and convert them to AVIF starting in ' . $sleepFor . ' seconds.' . "\n\n";
echo 'Press CTRL+C now if that is not what you want to do.' . "\n";

sleep($sleepFor);

if (empty($argv[1])) {
	echo "\n" . 'Critical error. Expected usage is `./avif-image-convert.php <collectionFolderName>`' . "\n";
	exit(1);
}

$collectionFolderName = $argv[1];
$collectionUtility = new Collection\Utility($systemState);
$collectionUtility->validateCollectionFolderName($collectionFolderName);
$collectionPath = $systemState->dirCollection . '/' . $collectionFolderName;

$collectionData = $collectionUtility->getCollectionDataRecursive($collectionFolderName);

foreach ($collectionData as $filepath => $metadata) {
	if (!in_array(pathinfo($filepath, PATHINFO_EXTENSION), ['jpg', 'jpeg'], true)) {
		continue;
	}

	// The file is a JPEG

	$jpegPath = $collectionPath . '/' . $filepath;
	$avifPath = $collectionPath . '/' . preg_replace('#\.jpe?g$#', '.avif', $filepath);

	if (!file_exists($avifPath)) {
		Module\Lipupini\Collection\MediaProcessor\Image::imagine()->open($jpegPath)
			->save($avifPath, $systemState->imageQuality)
		;
	} else {
		echo 'File exists: ' . $avifPath . "\n";
		continue;
	}

	if (!file_exists($avifPath)) {
		echo 'Could not convert `' . $jpegPath . '`...' . "\n";
	}
	unlink($jpegPath);
}

echo "\n";
echo 'WARNING: Rebuilding account cache starting in ' . $sleepFor . ' seconds.' . "\n\n";
echo 'Press CTRL+C now if that is not what you want to do.' . "\n";

sleep($sleepFor);

$command = $systemState->dirRoot . '/bin/process-media.php ' . escapeshellarg($collectionFolderName);
passthru($command);

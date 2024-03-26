#!/usr/bin/env php
<?php

use Module\Lipupini\Collection;
use Module\Lipupini\State;

/** @var State $systemState */
$systemState = require(__DIR__ . '/../config/state.php');

if (empty($argv[1])) {
	echo 'Must specify collection name' . "\n";
	exit(1);
}

$collectionFolderName = $argv[1];

(new Collection\Utility($systemState))->validateCollectionFolderName($collectionFolderName);

$collectionPath = $systemState->dirCollection . '/' . $collectionFolderName;
$lipupiniPath = $collectionPath . '/.lipupini';

if (
	file_exists($lipupiniPath . '/files.json')
) {
	echo 'File already exists: `collection/' . $collectionFolderName . '/.lipupini/files.json`' . "\n";
	exit(0);
}

// Create the `.lipupini` subfolder if needed
if (!is_dir($lipupiniPath)) {
	echo 'Creating `.lipupini` folder...' . "\n";
	mkdir($lipupiniPath, 0755, true);
}

echo 'Generating `collection/' . $collectionFolderName . '/.lipupini/files.json`...' . "\n";

$dir = new \DirectoryIterator($collectionPath);
$files = [];
foreach ($dir as $fileinfo) {
	if ($fileinfo->isDot()) {
		continue;
	}

	// Skip hidden files
	if ($fileinfo->getFilename()[0] === '.') {
		continue;
	}

	$fileName = $fileinfo->getFilename();

	$files[$fileName] = [
		'caption' => $fileName,
		//'date' => (new DateTime)->format(DateTime::ISO8601),
	];
}

file_put_contents($lipupiniPath . '/files.json', json_encode($files, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

echo 'Done.' . "\n";

exit(0);

#!/usr/bin/env php
<?php

require(__DIR__ . '/../package/vendor/autoload.php');

use Plugin\Lipupini\Collection;

if (empty($argv[1])) {
	echo 'Must specify collection name' . "\n";
	exit(1);
}

$collectionFolder = $argv[1];

$baseUri = 'http://localhost/';
$systemState = new Plugin\Lipupini\State(
	baseUri: $baseUri, // Include trailing slash
	cacheBaseUri: $baseUri . 'c/', // If you'd like to use another URL for static files (e.g. CDN), put that here
	frontendView: 'Lukinview',
	debug: true
);

(new Collection\Utility($systemState))->validateCollectionFolderName($collectionFolder);

$collectionPath = $systemState->dirCollection . '/' . $collectionFolder;
$lipupiniPath = $collectionPath . '/.lipupini';

// Create the `.lipupini` subfolder if needed
if (!is_dir($lipupiniPath)) {
	echo 'Creating `.lipupini` folder...' . "\n";
	mkdir($lipupiniPath, 0755, true);
}

if (
	file_exists($lipupiniPath . '/.files.json')
) {
	echo 'File already exists: `collection/' . $collectionFolder . '/.lipupini/.files.json`' . "\n";
} else {
	echo 'Generating `collection/' . $collectionFolder . '/.lipupini/.files.json`...' . "\n";

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

	file_put_contents($lipupiniPath . '/.files.json', json_encode($files, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

echo 'Done.' . "\n";

exit(0);

#!/usr/bin/env php
<?php

require(__DIR__ . '/../package/vendor/autoload.php');

use System\Lipupini;
use Plugin\Lipupini\Encryption;

// Expects a username as the only argument
$collectionFolder = $argv[1];

Lipupini::validateCollectionFolderName($collectionFolder);

$collectionPath = DIR_COLLECTION . '/' . $collectionFolder;
$lipupiniPath = $collectionPath . '/.lipupini';

// Create the `.lipupini` subfolder if needed
if (!is_dir($lipupiniPath)) {
	echo 'Creating `.lipupini` folder...' . "\n";
	mkdir($lipupiniPath, 0755, true);
}

// Generate RSA keypair if needed
if (
	!file_exists($lipupiniPath . '/.rsakey.public') ||
	!file_exists($lipupiniPath . '/.rsakey.private')
) {
	echo 'Did not find RSA keypair. Creating...' . "\n";
	$encryption = new Encryption(2048);
	$encryption->generateAndSave($lipupiniPath . '/.rsakey');
}

// Generate a basic `.files.json` if needed
if (
	!file_exists($lipupiniPath . '/.files.json')
) {
	echo 'Creating `.files.json`...' . "\n";

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

		$files[] = [
			'filename' => $fileinfo->getFilename(),
			'caption' => $fileinfo->getFilename(),
			//'date' => (new DateTime)->format(DateTime::ATOM),
		];
	}

	file_put_contents($lipupiniPath . '/.files.json', json_encode($files, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

// Reminder to add an avatar PNG
if (
	!file_exists($lipupiniPath . '/.avatar.png')
) {
	echo 'Be sure to add an avatar .png at `' . $lipupiniPath . '/.avatar.png`!' . "\n";
}

echo 'Done.' . "\n";

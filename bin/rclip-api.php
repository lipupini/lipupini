#!/usr/bin/env php
<?php

require(__DIR__ . '/../package/vendor/autoload.php');

use Plugin\Lipupini\Collection\RclipSearch;

$collectionFolderName = $argv[1] ?? '';
$query = $argv[2] ?? null;

if (!$collectionFolderName) {
	echo <<<HEREDOC
USAGE
=====

Build rclip search index for "example" collection:

> ./rclip-api.php example

Perform a search in "example" collection for cat pictures:

> ./rclip-api.php example 'cat'

HEREDOC;
	exit();
}

$rclipSearch = new RclipSearch(
	collectionFolderName: $collectionFolderName,
	rclipPath: DIR_ROOT . '/package/rclip/rclip-v1.7.3-x86_64.AppImage'
);

if ($query) {
	// If a query was specified, run it
	$rclipSearch->query($query);
} else {
	// Otherwise, just build the index
	echo 'Building index...' . "\n";
	$rclipSearch->buildIndex();
}

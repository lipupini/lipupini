#!/usr/bin/env php
<?php

require(__DIR__ . '/../package/vendor/autoload.php');

use Plugin\Lipupini\Collection\RclipSearch;

$collectionFolderName = $argv[1] ?? '';
$query = $argv[2] ?? null;
$limit = (int)($argv[3] ?? 50);
$preview = !empty($argv[4]) && $argv[4] === 'preview';

if (!$limit) {
	throw new Exception('Invalid limit');
}

if (!$collectionFolderName) {
	echo <<<HEREDOC
USAGE
=====

Build rclip search index for "example" collection:

> ./rclip-api.php example

Perform a search in "example" collection for cat pictures (default 50 results):

> ./rclip-api.php example 'Cat'

Perform a search in "example" collection for cat pictures and return only the top 10 results:

> ./rclip-api.php example 'Cat' 10

Perform a search in "example" collection for cat pictures, return only the top 10 results and show preview (if terminal supports iTerm2 Inline Images Protocol):

> ./rclip-api.php example 'Cat' 10 preview

If showing previews, you will need to rerun the search without preview in order to save it into the collection.

HEREDOC;
	exit();
}

$rclipSearch = new RclipSearch(
	collectionFolderName: $collectionFolderName,
	rclipPath: DIR_ROOT . '/package/rclip/rclip-v1.7.3-x86_64.AppImage'
);

if (!$query) {
	// If there's no query, just build the index
	echo 'Building index...' . "\n";
	$rclipSearch->buildIndex();
	exit();
}

if ($preview) {
	$rclipSearch->preview($query, $limit);
	exit();
}

// If a query was specified, run it
$results = $rclipSearch->query($query, $limit);

if (empty($results)) {
	echo 'No results' . "\n";
	return;
}

$saveData = [];
foreach ($results as &$path) {
	echo $path . "\n";
	$saveData[preg_replace('#^' . DIR_COLLECTION . '/' . $collectionFolderName . '/#', '', $path)] = ['caption' => pathinfo($path, PATHINFO_FILENAME)];
}

echo "\n";
$saveSearch = readline('Save search for @' . $collectionFolderName . '? [Y/n] ');
if (strtoupper($saveSearch) !== 'Y') {
	return;
}

$searchesFile = DIR_COLLECTION . '/' . $collectionFolderName . '/.lipupini/.savedSearches.json';
$searches = file_exists($searchesFile) ? json_decode(file_get_contents($searchesFile), true) : [];
$searches[$query] = $saveData;
file_put_contents($searchesFile, json_encode($searches, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
echo 'Saved search to ' . $searchesFile . "\n";

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

If showing previews, you will need to rerun the search without preview in order to save to portfolio.

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

foreach ($results as &$path) {
	echo $path . "\n";
	$path = [
			'filename' => preg_replace('#^' . DIR_COLLECTION . '/' . $collectionFolderName . '/#', '', $path),
			'caption' => pathinfo($path, PATHINFO_FILENAME),
	];
}

echo "\n";
$saveToPortfolio = readline('Save to portfolio for @' . $collectionFolderName . ' [Y/n]? ');
if (strtoupper($saveToPortfolio) !== 'Y') {
	return;
}

$portfolioFile = DIR_COLLECTION . '/' . $collectionFolderName . '/.lipupini/.portfolios.json';
$portfolios = file_exists($portfolioFile) ? json_decode(file_get_contents($portfolioFile), true) : [];
$portfolios[$query] = $results;
file_put_contents($portfolioFile, json_encode($portfolios, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
echo 'Wrote new portfolio to ' . $portfolioFile . "\n";

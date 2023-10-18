#!/usr/bin/env php
<?php

require(__DIR__ . '/../module/Lipupini/vendor/autoload.php');

$baseUri = 'http://localhost/';
$systemState = new Module\Lipupini\State(
	baseUri: $baseUri, // Include trailing slash
	cacheBaseUri: $baseUri . 'c/', // If you'd like to use another URL for static files (e.g. CDN), put that here
	frontendView: 'Lukinview',
	debug: true
);

$staticCache = $systemState->dirWebroot . parse_url($systemState->cacheBaseUri, PHP_URL_PATH);
$activityPubCache = $systemState->dirCollection . '/.apcache';
echo 'About to delete the following folders if they exist:' . "\n\n";
echo '1) ' . $staticCache . ' (Static media cache)' . "\n";
echo '2) ' . $activityPubCache . ' (ActivityPub cache)' . "\n\n";

$confirm = readline('Proceed? [Y/n] ');
if (strtoupper($confirm) !== 'Y') {
	return;
}

if (is_dir($staticCache)) {
	deleteDirectory($staticCache, $systemState);
}

if (is_dir($activityPubCache)) {
	deleteDirectory($activityPubCache, $systemState);
}

echo "\n" . 'Done.' . "\n";

exit(0);

function deleteDirectory($directory, $systemState) {
	if (empty($directory)) {
		throw new \Lipupini\Exception('No directory specified');
	}

	if (!$systemState->dirRoot || !str_starts_with($directory, $systemState->dirRoot)) {
		throw new \Lipupini\Exception('Expected directory within project');
	}

	passthru('rm -r ' . escapeshellarg($directory),$resultCode);

	if ($resultCode !== 0) {
		echo 'Could not delete directory: ' . $directory . "\n";
	}
}

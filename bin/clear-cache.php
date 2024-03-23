#!/usr/bin/env php
<?php

use Module\Lipupini\Exception;
use Module\Lipupini\State;

/** @var State $systemState */
$systemState = require(__DIR__ . '/../config/system.php');

$staticCache = $systemState->dirWebroot . parse_url($systemState->staticMediaBaseUri, PHP_URL_PATH);
$activityPubCache = $systemState->dirModule . '/Lipupini/ActivityPub/cache';
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
		throw new Exception('No directory specified');
	}

	if (!$systemState->dirRoot || !str_starts_with($directory, $systemState->dirRoot)) {
		throw new Exception('Expected directory within project');
	}

	passthru('rm -r ' . escapeshellarg($directory),$resultCode);

	if ($resultCode !== 0) {
		echo 'Could not delete directory: ' . $directory . "\n";
	}
}

#!/usr/bin/env php
<?php

require(__DIR__ . '/../module/Lipupini/vendor/autoload.php');

use Module\Lipupini\Collection;

if (empty($argv[1])) {
	echo 'Must specify collection name' . "\n";
	exit(1);
}

$collectionFolder = $argv[1];

$baseUri = 'http://localhost/';
$systemState = new Module\Lipupini\State(
	baseUri: $baseUri, // Include trailing slash
	cacheBaseUri: $baseUri . 'c/', // If you'd like to use another URL for static files (e.g. CDN), put that here
	frontendView: 'Lukinview',
	debug: true
);

(new Collection\Utility($systemState))->validateCollectionFolderName($collectionFolder);

$lipupiniPath = $systemState->dirCollection . '/' . $collectionFolder . '/.lipupini';

// Create the `.lipupini` subfolder if needed
if (!is_dir($lipupiniPath)) {
	echo 'Creating `.lipupini` folder...' . "\n";
	mkdir($lipupiniPath, 0755, true);
}

echo 'About to generate new RSA keys in `collection/' . $collectionFolder . '/.lipupini/`...' . "\n\n";

$confirm = readline('Proceed? [Y/n] ');
if (strtoupper($confirm) !== 'Y') {
	return;
}

(new Module\Lipupini\Encryption\Key)->generateAndSave(
	privateKeyPath: $lipupiniPath . '/.rsakey.private',
	publicKeyPath: $lipupiniPath . '/.rsakey.public',
	privateKeyBits: 2048,
);

echo "\n" . 'Done.' . "\n";

exit(0);

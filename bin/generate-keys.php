#!/usr/bin/env php
<?php

use Module\Lipupini\Collection;
use Module\Lipupini\State;
use Module\Lipupini\Encryption;

/** @var State $systemState */
$systemState = require(__DIR__ . '/../config/system.php');

if (empty($argv[1])) {
	echo 'Must specify collection name' . "\n";
	exit(1);
}

$collectionFolder = $argv[1];

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

(new Encryption\Key)->generateAndSave(
	privateKeyPath: $lipupiniPath . '/.rsakey.private',
	publicKeyPath: $lipupiniPath . '/.rsakey.public',
	privateKeyBits: 2048,
);

echo "\n" . 'Done.' . "\n";

exit(0);

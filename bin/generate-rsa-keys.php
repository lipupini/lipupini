#!/usr/bin/env php
<?php

use System\Lipupini;
use Plugin\Lipupini\Encryption;

require(__DIR__ . '/../package/vendor/autoload.php');

// Expects a username as the only argument
$collectionFolder = $argv[1];

Lipupini::validateCollectionFolderName($collectionFolder);

$encryption = new Encryption(2048);

$encryption->generateAndSave(DIR_COLLECTION . '/' . $collectionFolder . '/.lipupini/.rsakey');

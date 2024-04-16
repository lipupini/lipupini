#!/usr/bin/env php
<?php

use Module\Lipupini\State;
use Module\Lipupini\Collection\Utility;

// See `readline` note in root README.md as this script might benefit from prompts

/** @var State $systemState */
$systemState = require(__DIR__ . '/../system/config/state.php');

switch ($argv[1]) {
	case 'hasFfmpeg' :
		echo json_encode(Utility::hasFfmpeg($systemState));
		break;
	default:
		throw new Exception('No action specified');
}

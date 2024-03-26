<?php

use Module\Lipupini\Request;
use Module\Lipupini\State;

// `realpath` resolves symlinks and returns absolute path
$projectRootDir = realpath(__DIR__ . '/../../../');
/** @var State $systemState */
$systemState = require($projectRootDir . '/system/config/state.php');

return (new Request\Incoming\Queue(
	$systemState
))->render();

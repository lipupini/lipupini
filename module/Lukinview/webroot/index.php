<?php

use Module\Lipupini\Request;
use Module\Lipupini\State;

$projectRootDir = realpath(__DIR__ . '/../../../');
/** @var State $systemState */
$systemState = require($projectRootDir . '/config/state.php');

return (new Request\Incoming\Queue(
	$systemState
))->render();

<?php

$rootDir = realpath(__DIR__ . '/../../../');
require($rootDir . '/module/Lipupini/vendor/autoload.php');

use Module\Lipupini\Request;

return (new Request\Queue(
	require($rootDir . '/config/system.php')
))->render();

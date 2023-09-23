<?php

require(__DIR__ . '/../../../package/vendor/autoload.php');

// Other core directory constants are defined in `system/Initialize.php`
define('DIR_WEBROOT', __DIR__);
define('DIR_VIEW', realpath(DIR_WEBROOT . '/../'));

use System\Plugin;

echo 'Lipupini';

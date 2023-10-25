<?php
use Module\Lipupini\L18n\A;
A::$path = realpath(__DIR__ . '/../../');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlentities($this->pageTitle) ?></title>
<link rel="stylesheet" href="/css/Global.css">
<?php echo $this->htmlHead ?? '' ?>
</head>
<body>

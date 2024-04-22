<?php
/*
 * This file is for tinkering with special characters in pathnames and not part of the main tests
 */

ini_set('display_errors', 1);

if (isset($_GET['debug'])) {
	echo htmlentities($_SERVER['REQUEST_URI']) . '<hr><hr><hr><hr><hr>';
	//echo htmlentities(urldecode($_SERVER['REQUEST_URI'])) . '<hr><hr><hr><hr><hr>';
	echo htmlentities(rawurldecode($_SERVER['REQUEST_URI'])) . '<hr><hr><hr><hr><hr>';
	exit();
}

if (pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION)) {
	return false;
}

use Module\Lipupini\Collection\Utility;

require(__DIR__ . '/../../module/Lipupini/vendor/autoload.php');

$collectionUtility = new Utility(
	require(__DIR__ . '/../../system/config/state.php')
);

$baseUri = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/';
$testData = [];
$testData['name'] = '!ñ\'@ #$+á%é^&()/!ñ\'@ #$+á%é^&().png';
$testData['filePath'] = __DIR__ . '/' . $testData['name'];
$testData['urlPath'] = $baseUri . $testData['name'];
$testData['encoding']['urlencode'] = [
	//'urlPath' => urlencode($testData['urlPath']),
	//'urlPathRelative' => urlencode('/' . $testData['name']),
];
$testData['encoding']['rawurlencode'] = [
	//'urlPath' => rawurlencode($testData['urlPath']),
	// http://localhost:1234/%21%C3%B1%27%40%23%24%22%C3%A1%25%C3%A9%5E%26%2A%28%29%2F%21%C3%B1%27%40%23%24%22%C3%A1%25%C3%A9%5E%26%2A%28%29.png
	'urlPathRelative' => rawurlencode($testData['name']),
];
$testData['encoding']['htmlentities'] = [
	//'urlPath' => htmlentities($testData['urlPath']),
	//'urlPathRelative' => htmlentities('/' . $testData['name']),
];
$testData['encoding']['htmlentitiesUrl'] = [
	//'urlPath' => htmlentitiesUrl($testData['urlPath']),
	//'urlPathRelative' => htmlentitiesUrl('/' . $testData['name']),
];
$testData['encoding']['urlEncodeUrl'] = [
	// http://localhost:1234/%21%C3%B1%27%40%23%24%22%C3%A1%25%C3%A9%5E%26%2A%28%29/%21%C3%B1%27%40%23%24%22%C3%A1%25%C3%A9%5E%26%2A%28%29.png
	'urlPath' => $collectionUtility::urlEncodeUrl($testData['urlPath']),
	//'urlPathRelative' => $collectionUtility::urlEncodeUrl('/' . $testData['name']),
];

if (isset($_GET['img'])) {
	$imgData = file_get_contents($testData['filePath']);
	header('Content-type: image/png');
	echo $imgData;
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
	<style>
		.background-test {
			border: 1px solid #000;
			width: 200px;
			height: 200px;
			background-size: cover;
		}
	</style>
</head>
<body>
<?php
var_dump($testData);

foreach ($testData['encoding'] as $encodingType => $encodingTypeData) {
	foreach ($encodingTypeData as $urlType => $url) : ?>
<hr><hr><hr><hr><hr><hr><hr><hr><hr><hr>
&lt;img&gt; <?php echo $encodingType ?> <?php echo $urlType ?>: <?php echo htmlentities($url) ?><br>
<img src="<?php echo $url ?>">
<hr>
&lt;div&gt; <?php echo $encodingType ?> <?php echo $urlType ?>: <?php echo htmlentities($url) ?><br>
<div class="background-test" style="background-image:url(<?php echo $url ?>)"></div>
<hr>
&lt;a&gt; <?php echo $encodingType ?> <?php echo $urlType ?>:
<a href="<?php echo $url ?>"><?php echo htmlentities($url) ?></a>
<hr><hr><hr><hr><hr><hr><hr><hr><hr><hr>
</body>
</html>

<?php
	endforeach;
}

function htmlentitiesUrl(string $url) {
	// If it's only a path and not a full URL, do it this way instead
	if (empty(parse_url($url, PHP_URL_HOST))) {
		return join('/', array_map('htmlentities', explode('/', $url)));
	}

	// `parse_url` does not handle all the filepath characters that Lipupini wants to support
	// So we can't use `PHP_URL_PATH' key from that, instead we have to use RegExp
	return preg_replace_callback('#://([^/]+)/([^?]+)#', function ($match) {
		return '://' . $match[1] . '/' . htmlentitiesUrl($match[2]);
	}, $url);
}

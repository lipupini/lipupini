<?php

use Module\Lipupini\Collection;
use Module\Lipupini\L18n\A;

$collectionUtility = new Collection\Utility($this->system);
$mediaTypesByExtension = $collectionUtility->mediaTypesByExtension();
$extension = pathinfo($this->collectionFilePath, PATHINFO_EXTENSION);

if ($mediaTypesByExtension[$extension]['mediaType'] === 'video') {
	$this->htmlHead .=
		'<link rel="stylesheet" href="/lib/videojs/video-js.min.css">' . "\n" .
		'<script src="/lib/videojs/video.min.js"></script>' . "\n"
	;
}

$urlEncodedFilename = implode('/', array_map('rawurlencode', explode('/', $this->collectionFilePath)));
$parentPathLastSegment = explode('/', $this->parentPath)[substr_count($this->parentPath, '/')];

require(__DIR__ . '/../Core/Open.php') ?>

<div id="media-item" class="<?php echo htmlentities($this->mediaType) ?>-item">
<header>
	<nav>
		<div class="pagination parent"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"></a></div>
	</nav>
</header>
<main>
<?php
switch ($mediaTypesByExtension[$extension]['mediaType']) :
case 'audio' : ?>

<div class="audio-container">
	<div class="caption"><span><?php echo htmlentities($this->fileData['caption']) ?></span></div>
	<audio controls="controls" preload="metadata">
		<source src="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/audio/' . $urlEncodedFilename) ?>" type="<?php echo htmlentities($mediaTypesByExtension[$extension]['mimeType']) ?>">
	</audio>
	<div class="waveform" style="background-image:url('<?php echo $collectionUtility::urlEncodeUrl($this->fileData['waveform'] ?? '') ?>')">
		<div class="elapsed hidden"></div>
	</div>
	<?php if (!empty($this->fileData['thumbnail'])) : ?>

	<img src="<?php echo htmlentities($this->fileData['thumbnail']) ?>">
	<?php endif ?>

</div>
<script src="/js/AudioVideo.js?v=<?php echo FRONTEND_CACHE_VERSION ?>"></script>
<script src="/js/AudioWaveformSeek.js?v=<?php echo FRONTEND_CACHE_VERSION ?>"></script>
<?php break;
case 'image' : ?>

<a href="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/image/large/' . $urlEncodedFilename) ?>" target="_blank" class="image-container">
	<img src="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/image/medium/' . $urlEncodedFilename) ?>" title="<?php echo htmlentities($this->fileData['caption']) ?>">
</a>
<?php break;
case 'text' : ?>

<div class="text-container">
	<object type="text/html" data="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/text/html/' . $urlEncodedFilename) ?>.html"></object>
</div>
<?php break;
case 'video' : ?>

<div class="video-container">
	<video class="video-js" controls="" preload="metadata" loop="" title="<?php echo htmlentities($this->fileData['caption']) ?>" poster="<?php echo htmlentities($this->fileData['thumbnail'] ?? '') ?>" data-setup="{}">
		<source src="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/video/' . $urlEncodedFilename) ?>" type="<?php echo htmlentities($mediaTypesByExtension[$extension]['mimeType']) ?>">
	</video>
</div>
<?php break;
endswitch;
?>

</main>
<footer>
	<div class="about">
		<a href="https://github.com/lipupini/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="<?php echo A::z('More information about this software') ?>">?</a>
	</div>
</footer>
</div>

<?php require(__DIR__ . '/../Core/Close.php') ?>

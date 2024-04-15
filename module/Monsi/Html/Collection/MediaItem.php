<?php

use Module\Lipupini\Collection;
use Module\Lipupini\L18n\A;

$collectionUtility = new Collection\Utility($this->system);
$mediaTypesByExtension = $collectionUtility->mediaTypesByExtension();
$extension = pathinfo($this->collectionFileName, PATHINFO_EXTENSION);

if ($mediaTypesByExtension[$extension]['mediaType'] === 'video') {
	$this->htmlHead .= '<link rel="stylesheet" href="/lib/videojs/video-js.min.css">' . "\n"
	                .  '<script src="/lib/videojs/video.min.js"></script>' . "\n";
}

$urlEncodedFilename = implode('/', array_map('rawurlencode', explode('/', $this->collectionFileName)));
$parentPathLastSegment = explode('/', $this->parentPath)[substr_count($this->parentPath, '/')];

require(__DIR__ . '/../Core/Open.php') ?>

<main id="document" class="<?php echo htmlentities($this->mediaType) ?>-document">
<header>
	<nav>
		<div class="index pagination"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"></a></div>
	</nav>
</header>
<div id="media-item">
<?php
switch ($mediaTypesByExtension[$extension]['mediaType']) :
case 'audio' : ?>

<div class="audio-container audio-waveform-seek">
	<div class="caption"><span><?php echo htmlentities($this->fileData['caption']) ?></span></div>
	<audio controls="controls" preload="metadata">
		<source src="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/audio/' . $urlEncodedFilename) ?>" type="<?php echo htmlentities($mediaTypesByExtension[$extension]['mimeType']) ?>">
	</audio>
	<div class="waveform" style="background-image:url('<?php echo addslashes($this->system->staticMediaBaseUri . $this->collectionName . '/audio/waveform/' . $urlEncodedFilename . '.png') ?>')">
		<div class="elapsed hidden"></div>
	</div>
	<?php if (!empty($this->fileData['thumbnail'])) : ?>

	<img src="<?php echo htmlentities($this->fileData['thumbnail']) ?>">
	<?php endif ?>

</div>
<?php break;
case 'image' : ?>

<a href="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/image/large/' . $urlEncodedFilename) ?>" target="_blank" class="image-container">
	<img src="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/image/large/' . $urlEncodedFilename) ?>" title="<?php echo htmlentities($this->fileData['caption']) ?>">
</a>
<?php break;
case 'text' : ?>

<div class="text-container">
	<object type="text/html" data="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/text/' . $urlEncodedFilename) ?>.html"></object>
</div>
<?php break;
case 'video' : ?>

<div class="video-container">
	<video class="video-js" controls="" preload="metadata" loop="" title="<?php echo htmlentities($this->fileData['caption']) ?>" poster="<?php echo htmlentities($this->fileData['thumbnail']) ?>" data-setup="{}">
		<source src="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionName . '/video/' . $urlEncodedFilename) ?>" type="<?php echo htmlentities($mediaTypesByExtension[$extension]['mimeType']) ?>">
	</video>
</div>
<?php break;
endswitch;
?>

</div>
<script src="/js/audio-waveform-seek.js"></script>
</main>
<footer>
	<div class="about">
		<a href="https://github.com/lipupini/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="<?php echo A::z('More information about this software') ?>">?</a>
	</div>
</footer>

<?php require(__DIR__ . '/../Core/Close.php') ?>

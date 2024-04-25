<?php

use Module\Lipupini\Collection;
use Module\Lipupini\L18n\A;

$collectionUtility = new Collection\Utility($this->system);
$parentPathLastSegment = explode('/', preg_replace('#\?.*$#', '', $this->parentPath))[substr_count($this->parentPath, '/')];

require(__DIR__ . '/../Core/Open.php') ?>

<div id="media-item" class="<?php echo htmlentities($this->fileData['mediaType']) ?>-item">
<header>
	<nav>
		<div class="pagination parent"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"></a></div>
	</nav>
</header>
<main>
<?php
switch ($this->fileData['mediaType']) :
case 'audio' : ?>

<div class="audio-container">
	<div class="caption"><span><?php echo htmlentities($this->fileData['caption']) ?></span></div>
	<audio controls="controls" preload="metadata">
		<source src="<?php echo $collectionUtility::urlEncodeUrl($collectionUtility->assetUrl($this->collectionName, 'audio', $this->collectionFilePath)) ?>" type="<?php echo htmlentities($this->fileData['mimeType']) ?>">
	</audio>
	<div class="waveform" style="background-image:url('<?php echo $collectionUtility::urlEncodeUrl($this->fileData['waveform'] ?? '') ?>')">
		<div class="elapsed hidden"></div>
	</div>
	<?php if (!empty($this->fileData['thumbnail'])) : ?>

	<img src="<?php echo $collectionUtility::urlEncodeUrl($this->fileData['thumbnail']) ?>">
	<?php endif ?>

</div>
<script src="/js/AudioVideo.js?v=<?php echo FRONTEND_CACHE_VERSION ?>" async></script>
<script src="/js/AudioWaveformSeek.js?v=<?php echo FRONTEND_CACHE_VERSION ?>" async></script>
<?php break;
case 'image' : ?>

<a href="<?php echo $collectionUtility::urlEncodeUrl($collectionUtility->assetUrl($this->collectionName, 'image/large', $this->collectionFilePath)) ?>" target="_blank" class="image-container">
	<img src="<?php echo $collectionUtility::urlEncodeUrl($collectionUtility->assetUrl($this->collectionName, 'image/medium', $this->collectionFilePath)) ?>" title="<?php echo htmlentities($this->fileData['caption']) ?>">
</a>
<?php break;
case 'text' : ?>

<div class="text-container">
	<object type="text/html" data="<?php echo $collectionUtility::urlEncodeUrl($collectionUtility->assetUrl( $this->collectionName, 'text/html', $this->collectionFilePath)) ?>.html"></object>
</div>
<?php break;
case 'video' : ?>

<div class="video-container">
	<video class="video-js" controls loop preload="metadata" title="<?php echo htmlentities($this->fileData['caption']) ?>" poster="<?php echo $collectionUtility::urlEncodeUrl($this->fileData['thumbnail'] ?? '') ?>" data-setup="{}">
		<source src="<?php echo $collectionUtility::urlEncodeUrl($collectionUtility->assetUrl($this->collectionName, 'video', $this->collectionFilePath)) ?>" type="<?php echo htmlentities($this->fileData['mimeType']) ?>">
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

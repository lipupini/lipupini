<?php

use Module\Lipupini\Collection;
use Module\Lipupini\L18n\A;

$collectionUtility = new Collection\Utility($this->system);
$mediaTypesByExtension = $collectionUtility->mediaTypesByExtension();

require(__DIR__ . '/../Core/Open.php') ?>

<main id="document" class="<?php echo htmlentities($this->mediaType) ?>-document">
<header>
	<nav>
		<div class="index pagination"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($this->parentPath) : A::z('Homepage') ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($this->parentPath) : A::z('Homepage') ?>"></a></div>
	</nav>
</header>
<div id="media-item">
<?php
$extension = pathinfo($this->collectionFileName, PATHINFO_EXTENSION);
switch ($mediaTypesByExtension[$extension]['mediaType']) :
case 'audio' : ?>

<div class="audio-container">
	<audio id="audio-<?php echo sha1($this->collectionFileName) ?>" class="video-js vjs-default-skin"></audio>
	<script>
		let audio<?php echo sha1($this->collectionFileName) ?> = videojs('audio-<?php echo sha1($this->collectionFileName) ?>', wavesurferOptions, function() {
			audio<?php echo sha1($this->collectionFileName) ?>.src({src: '<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionFolderName . '/audio/' . $this->collectionFileName) ?>', type: '<?php echo htmlentities($mediaTypesByExtension[$extension]['mimeType']) ?>'});
		});
	</script>
</div>
<?php break;
case 'image' : ?>

<a href="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionFolderName . '/image/large/' . $this->collectionFileName) ?>" target="_blank" class="image-container">
	<img src="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionFolderName . '/image/large/' . $this->collectionFileName) ?>" title="<?php echo htmlentities($this->fileData['caption']) ?>">
</a>
<?php break;
case 'text' : ?>

<div class="text-container">
	<object type="text/html" data="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionFolderName . '/text/' . $this->collectionFileName) ?>.html"></object>
</div>
<?php break;
case 'video' : ?>

<div class="video-container">
	<video class="video-js" controls="" preload="metadata" loop="" title="<?php echo htmlentities($this->fileData['caption']) ?>" poster="<?php echo htmlentities($this->fileData['thumbnail']) ?>" data-setup="{}">
		<source src="<?php echo htmlentities($this->system->staticMediaBaseUri . $this->collectionFolderName . '/video/' . $this->collectionFileName) ?>" type="<?php echo htmlentities($mediaTypesByExtension[$extension]['mimeType']) ?>">
	</video>
</div>
<?php break;
endswitch;
?>
</div>
<footer>
	<div class="about">
		<a href="https://github.com/lipupini/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="<?php echo A::z('More information about this software') ?>">?</a>
	</div>
</footer>
</main>

<?php require(__DIR__ . '/../Core/Close.php') ?>

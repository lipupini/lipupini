<?php

use Module\Lipupini\Collection\Utility;
use Module\Lipupini\L18n\A;

$collectionUtility = new Utility($this->system);
$parentPathLastSegment = explode('/', preg_replace('#\?.*$#', '', $this->parentPath))[substr_count($this->parentPath, '/')];

require(__DIR__ . '/../Core/Open.php') ?>

<div id="folder">
<header>
	<nav>
		<div class="pagination previous"><a href="<?php echo $this->prevUrl ? htmlentities($this->prevUrl) : 'javascript:void(0)' ?>" class="button" title="<?php echo A::z('Previous') ?>"<?php if (! $this->prevUrl) : ?> disabled<?php endif ?>><img src="/img/arrow-left-bold.svg" alt="<?php echo A::z('Previous') ?>"></a></div>
		<div class="pagination parent"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"></a></div>
		<div class="pagination next"><a href="<?php echo $this->nextUrl ? htmlentities($this->nextUrl) : 'javascript:void(0)' ?>" class="button" title="<?php echo A::z('Next') ?>"<?php if (!$this->nextUrl) : ?> disabled<?php endif ?>><img src="/img/arrow-right-bold.svg" alt="<?php echo A::z('Next') ?>"></a></div>
	</nav>
</header>
<main class="grid">
<?php
foreach ($this->collectionData as $filename => $item) :
$extension = pathinfo($filename, PATHINFO_EXTENSION);
if ($extension) :
switch ($item['mediaType']) :
case 'audio' :
	$style = !empty($item['thumbnail']) ? ' style="background-image:url(\'' .  $collectionUtility::urlEncodeUrl($item['thumbnail'])  . '\')"' : '';
?>

<div class="audio-container"<?php echo $style ?> title="<?php echo htmlentities($item['caption']) ?>">
	<div class="caption">
		<a href="/@<?php echo $collectionUtility::urlEncodeUrl($this->collectionName . '/' . $filename) ?>.html">
			<span class="playing-indicator">➤</span>
			<?php echo htmlentities($item['caption']) ?>

		</a>
	</div>
	<div class="waveform" style="background-image:url('<?php echo $collectionUtility::urlEncodeUrl($item['waveform'] ?? '') ?>')">
		<div class="elapsed hidden"></div>
		<audio controls="controls" preload="metadata">
			<source src="<?php echo $collectionUtility::urlEncodeUrl($this->system->staticMediaBaseUri . $this->collectionName . '/audio/' . $filename) ?>" type="<?php echo htmlentities($item['mimeType']) ?>">
		</audio>
	</div>
</div>
<?php break;
case 'image' : ?>

<a
	class="image-container"
	href="/@<?php echo $collectionUtility::urlEncodeUrl($this->collectionName . '/' . $filename) ?>.html"
	title="<?php echo htmlentities($item['caption']) ?>"
	style="background-image:url('<?php echo $collectionUtility::urlEncodeUrl($collectionUtility->assetUrl($this->collectionName, 'image/thumbnail', $filename)) ?>')"
></a>
<?php break;
case 'text' : ?>

<a class="text-container" href="/@<?php echo $collectionUtility::urlEncodeUrl($this->collectionName . '/' . $filename) ?>.html">
	<div><?php echo htmlentities($item['caption']) ?></div>
</a>
<?php break;
case 'video' : ?>

<div class="video-container" title="<?php echo htmlentities($item['caption']) ?>">
	<div class="caption"><a href="/@<?php echo $collectionUtility::urlEncodeUrl($this->collectionName . '/' . $filename) ?>.html"><?php echo htmlentities($item['caption']) ?></a></div>
	<video class="video-js" controls="" preload="metadata" loop="" poster="<?php echo $collectionUtility::urlEncodeUrl($item['thumbnail'] ?? '') ?>" data-setup="{}">
		<source src="<?php echo $collectionUtility::urlEncodeUrl($this->system->staticMediaBaseUri . $this->collectionName . '/video/' . $filename) ?>" type="<?php echo htmlentities($item['mimeType']) ?>">
	</video>
</div>
<?php break;
endswitch;
else : ?>

<a class="folder-container" href="/@<?php echo $collectionUtility::urlEncodeUrl($this->collectionName . '/' . $filename) ?>" title="<?php echo htmlentities($item['caption']) ?>">
	<span><?php echo htmlentities($item['caption']) ?></span>
</a>
<?php endif;
endforeach ?>

</main>
<footer>
	<nav>
		<div class="pagination previous"><a href="<?php echo $this->prevUrl ? htmlentities($this->prevUrl) : 'javascript:void(0)' ?>" class="button" title="<?php echo A::z('Previous') ?>"<?php if (!$this->prevUrl) : ?> disabled<?php endif ?>><img src="/img/arrow-left-bold.svg" alt="<?php echo A::z('Previous') ?>"></a></div>
		<div class="pagination parent"></div>
		<div class="pagination next"><a href="<?php echo $this->nextUrl ? htmlentities($this->nextUrl) : 'javascript:void(0)' ?>" class="button" title="<?php echo A::z('Next') ?>"<?php if (!$this->nextUrl) : ?> disabled<?php endif ?>><img src="/img/arrow-right-bold.svg" alt="<?php echo A::z('Next') ?>"></a></div>
	</nav>
	<div class="about">
		<a href="https://github.com/lipupini/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="<?php echo A::z('More information about this software') ?>">?</a>
	</div>
</footer>
</div>

<?php require(__DIR__ . '/../Core/Close.php') ?>

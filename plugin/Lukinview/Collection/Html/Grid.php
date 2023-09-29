<header class="app-bar">
	<div></div>
	<div class="previous"><a href="<?php echo htmlentities($this->prevUrl) ?>" class="button" title="Previous"<?php if ($this->prevUrl === 'javascript:void(0)') : ?> disabled<?php endif ?>>←</a></div>
	<div class="index"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($this->parentPath) : 'Homepage' ?>">↑</a></div>
	<div class="next"><a href="<?php echo htmlentities($this->nextUrl) ?>" class="button" title="Next"<?php if ($this->nextUrl === 'javascript:void(0)') : ?> disabled<?php endif ?>>→</a></div>
	<div class="about"><a href="https://github.com/instalution/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="More information about this software">?</a></div>
</header>
<div id="media-container" class="grid square"></div>
<footer class="app-bar">
	<div></div>
	<div class="previous"><a href="<?php echo htmlentities($this->prevUrl) ?>" class="button" title="Previous"<?php if ($this->prevUrl === 'javascript:void(0)') : ?> disabled<?php endif ?>>←</a></div>
	<div></div>
	<div class="next"><a href="<?php echo htmlentities($this->nextUrl) ?>" class="button" title="Next"<?php if ($this->nextUrl === 'javascript:void(0)') : ?> disabled<?php endif ?>>→</a></div>
	<div></div>
</footer>
<script>let collectionData = <?php echo json_encode($this->collectionData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?></script>
<script type="module" src="/js/components/Grid/Grid.js"></script>

<header class="app-bar">
	<!--<div id="button-container-settings"><a class="button" title="Settings">⚙</a></div>-->
	<div class="previous"><a href="<?php echo $this->prevUrl ?>" class="button" title="Previous"<?php if ($this->prevUrl === 'javascript:return false') : ?> disabled<?php endif ?>>←</a></div>
	<div class="index"><a href="/" class="button" title="Domain index">↑</a></div>
	<div class="next"><a href="<?php echo $this->nextUrl ?>" class="button" title="Next"<?php if ($this->nextUrl === 'javascript:return false') : ?> disabled<?php endif ?>>→</a></div>
	<!--<div class="about"><a href="https://github.com/instalution/lipupini" target="_blank" class="button" title="More information about this software">?</a></div>-->
</header>
<div id="media-container" class="grid square"></div>
<footer class="app-bar">
	<div class="previous"><a href="<?php echo $this->prevUrl ?>" class="button" title="Previous"<?php if ($this->prevUrl === 'javascript:return false') : ?> disabled<?php endif ?>>←</a></div>
	<div class="index"><a href="javascript:window.scrollTo(0, 0)" class="button" title="Top of page">↑</a></div>
	<div class="next"><a href="<?php echo $this->nextUrl ?>" class="button" title="Next"<?php if ($this->nextUrl === 'javascript:return false') : ?> disabled<?php endif ?>>→</a></div>
</footer>
<script>let collectionData = <?php echo json_encode($this->collectionData, JSON_UNESCAPED_SLASHES) ?></script>
<script type="module" src="/js/components/Grid/Grid.js"></script>

<main class="media-item">
<header class="app-bar">
	<div></div>
	<div class="previous pagination"></div>
	<div class="index pagination"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($this->parentPath) : 'Homepage' ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($this->parentPath) : 'Homepage' ?>"></a></div>
	<div class="next pagination"></div>
	<div class="about"><a href="https://github.com/instalution/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="More information about this software">?</a></div>
</header>
<div id="media-item"></div>
<script>let collection = '<?php echo htmlentities($this->collectionFolderName) ?>';let filename = '<?php echo htmlentities($this->collectionPath) ?>';let fileData = <?php echo json_encode($this->fileData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?>;</script>
<script type="module" src="/js/components/Document/Document.js"></script>
</main>

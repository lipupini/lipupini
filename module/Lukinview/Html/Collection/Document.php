<?php

use Module\Lipupini\Collection;

require(__DIR__ . '/../Core/Open.php') ?>

<main class="media-item">
<header class="app-bar">
	<div></div>
	<div class="previous pagination"></div>
	<div class="index pagination"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($this->parentPath) : 'Homepage' ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($this->parentPath) : 'Homepage' ?>"></a></div>
	<div class="next pagination"></div>
	<div class="about"><a href="https://github.com/instalution/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="More information about this software">?</a></div>
</header>
<div id="media-item"></div>
<script>let baseUri = '<?php echo htmlentities($this->system->cacheBaseUri) ?>';let collection = '<?php echo htmlentities($this->system->requests[Collection\Request::class]->folderName) ?>';let filename = '<?php echo htmlentities($this->collectionFileName) ?>';let fileData = <?php echo json_encode($this->fileData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?>;</script>
<script type="module">
import van from '/lib/van-1.2.1.min.js'
import { Document } from '/js/components/Document.js'
van.add(document.getElementById('media-item'), Document({collection, baseUri, filename, data: fileData}))
</script>
</main>

<?php require(__DIR__ . '/../Core/Close.php') ?>

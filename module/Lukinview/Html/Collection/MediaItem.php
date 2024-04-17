<?php

use Module\Lipupini\Collection;
use Module\Lipupini\L18n\A;

$parentPathLastSegment = explode('/', $this->parentPath)[substr_count($this->parentPath, '/')];

require(__DIR__ . '/../Core/Open.php') ?>

<div id="media-item" class="<?php echo htmlentities($this->mediaType) ?>-item">
<header class="app-bar">
	<nav>
		<div class="pagination parent"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($parentPathLastSegment) : A::z('Homepage') ?>"></a></div>
	</nav>
</header>
<main></main>
<script>let baseUri = '<?php echo addslashes($this->system->staticMediaBaseUri) ?>';let collection = '<?php echo addslashes($this->system->request[Collection\Request::class]->name) ?>';let filename = '<?php echo addslashes($this->collectionFileName) ?>';let fileData = <?php echo json_encode($this->fileData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?>;let fileTypes=<?php echo json_encode($this->system->mediaType, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?>;</script>
<script type="module">
import van from '/lib/van-1.5.0.min.js'
import { Document } from '/js/component/Document.js'
van.add(document.querySelector('#media-item main'), Document({collection, baseUri, filename, data: fileData}))
</script>
<footer>
	<div class="about">
		<a href="https://github.com/lipupini/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="<?php echo A::z('More information about this software') ?>">?</a>
	</div>
</footer>
</div>

<?php require(__DIR__ . '/../Core/Close.php') ?>

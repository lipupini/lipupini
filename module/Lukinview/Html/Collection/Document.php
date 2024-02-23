<?php

use Module\Lipupini\Collection;
use Module\Lipupini\L18n\A;

require(__DIR__ . '/../Core/Open.php') ?>

<main class="media-item">
<header class="app-bar">
	<div></div>
	<div class="previous pagination"></div>
	<div class="index pagination"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($this->parentPath) : A::z('Homepage') ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($this->parentPath) : A::z('Homepage') ?>"></a></div>
	<div class="next pagination"></div>
	<div class="about"><a href="https://github.com/lipupini/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="<?php echo A::z('More information about this software') ?>">?</a></div>
</header>
<div id="media-item"></div>
<script>let baseUri = '<?php echo htmlentities($this->system->staticMediaBaseUri) ?>';let collection = '<?php echo htmlentities($this->system->requests[Collection\Request::class]->folderName) ?>';let filename = '<?php echo htmlentities(rawurldecode($this->collectionFileName)) ?>';let fileData = <?php echo json_encode($this->fileData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?>;let fileTypes=<?php echo json_encode($this->fileTypes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?>;</script>
<script type="module">
import van from '/lib/van-1.2.7.min.js'
import { Document } from '/js/components/Document.js'
van.add(document.getElementById('media-item'), Document({collection, baseUri, filename, data: fileData}))
</script>
</main>

<?php require(__DIR__ . '/../Core/Close.php') ?>

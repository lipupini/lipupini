<?php

use Module\Lipupini\L18n\A;

require(__DIR__ . '/../Core/Open.php') ?>

<main class="media-grid">
<header class="app-bar">
	<div></div>
	<div class="previous pagination"><a href="<?php echo $this->prevUrl ? htmlentities($this->prevUrl) : 'javascript:void(0)' ?>" class="button" title="<?php echo A::z('Previous') ?>"<?php if (! $this->prevUrl) : ?> disabled<?php endif ?>><img src="/img/arrow-left-bold.svg" alt="<?php echo A::z('Previous') ?>"></a></div>
	<div class="index pagination"><a href="/<?php echo htmlentities($this->parentPath) ?>" class="button" title="<?php echo $this->parentPath ? htmlentities($this->parentPath) : A::z('Homepage') ?>"><img src="/img/arrow-up-bold.svg" alt="<?php echo $this->parentPath ? htmlentities($this->parentPath) : A::z('Homepage') ?>"></a></div>
	<div class="next pagination"><a href="<?php echo $this->nextUrl ? htmlentities($this->nextUrl) : 'javascript:void(0)' ?>" class="button" title="<?php echo A::z('Next') ?>"<?php if (!$this->nextUrl) : ?> disabled<?php endif ?>><img src="/img/arrow-right-bold.svg" alt="<?php echo A::z('Next') ?>"></a></div>
	<div class="about">
		<a href="https://github.com/instalution/lipupini" target="_blank" rel="noopener noreferrer" class="button" title="<?php echo A::z('More information about this software') ?>">?</a>
	</div>
</header>
<div id="media-grid" class="grid square"></div>
<script>let baseUri='<?php echo htmlentities($this->system->staticMediaBaseUri) ?>';let collection='<?php echo htmlentities($this->collectionFolderName) ?>';let collectionData=<?php echo json_encode($this->collectionData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?>;let fileTypes=<?php echo json_encode($this->fileTypes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?></script>
<script type="module">
import { Folder } from '/js/components/Folder.js'
Folder({collection, collectionData, baseUri})
</script>
<footer class="app-bar">
	<div></div>
	<div class="previous pagination"><a href="<?php echo $this->prevUrl ? htmlentities($this->prevUrl) : 'javascript:void(0)' ?>" class="button" title="<?php echo A::z('Previous') ?>"<?php if (!$this->prevUrl) : ?> disabled<?php endif ?>><img src="/img/arrow-left-bold.svg" alt="<?php echo A::z('Previous') ?>"></a></div>
	<div></div>
	<div class="next pagination"><a href="<?php echo $this->nextUrl ? htmlentities($this->nextUrl) : 'javascript:void(0)' ?>" class="button" title="<?php echo A::z('Next') ?>"<?php if (!$this->nextUrl) : ?> disabled<?php endif ?>><img src="/img/arrow-right-bold.svg" alt="<?php echo A::z('Next') ?>"></a></div>
	<div></div>
</footer>
</main>

<?php require(__DIR__ . '/../Core/Close.php') ?>

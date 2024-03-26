<?php

use Module\Lipupini\L18n\A;

$localCollections = $this->getLocalCollections();

require(__DIR__ . '/Core/Open.php');

if (!empty($localCollections)) : ?>

<ul>
	<?php foreach ($localCollections as $localCollection) : ?>

	<li><a href="/@<?php echo htmlentities($localCollection) ?>"><?php echo htmlentities($localCollection) ?></a></li>
	<?php endforeach ?>

</ul>
<?php else : ?>

<div class="full-page-center-text">
	<a href="https://github.com/lipupini/lipupini/#add-your-collection" target="_blank" rel="noopener noreferrer"><?php echo A::z('Add your collection') ?></kbd></a>
</div>
<?php endif ?>

<?php require(__DIR__ . '/Core/Close.php') ?>

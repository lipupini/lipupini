<?php

use Module\Lipupini\L18n\A;

require(__DIR__ . '/../Core/Open.php');

if (!empty($this->collectionNames)) : ?>

<ul>
	<?php foreach ($this->collectionNames as $collectionName) : ?>

	<li><a href="/@/<?php echo htmlentities($collectionName) ?>"><?php echo htmlentities($collectionName) ?></a></li>
	<?php endforeach ?>

</ul>
<?php else : ?>

<div class="add-collection">
	<a href="https://github.com/lipupini/lipupini/#add-your-collection" target="_blank" rel="noopener noreferrer"><?php echo A::z('Add your collection') ?></kbd></a>
</div>
<?php endif ?>

<?php require(__DIR__ . '/../Core/Close.php') ?>

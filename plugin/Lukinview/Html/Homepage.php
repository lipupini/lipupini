<?php require(__DIR__ . '/Core/Open.php') ?>

<ul>
	<?php foreach ($this->getLocalCollections() as $localCollection) : ?>

	<li><a href="/@<?php echo htmlentities($localCollection) ?>"><?php echo htmlentities($localCollection) ?></a></li>
	<?php endforeach ?>

</ul>

<?php require(__DIR__ . '/Core/Close.php') ?>

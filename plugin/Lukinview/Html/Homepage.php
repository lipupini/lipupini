<h1>Lipupini</h1>

<ul>
	<?php foreach ($this->getLocalCollections() as $localCollection) : ?>

	<li><a href="/@<?php echo htmlentities($localCollection) ?>"><?php echo htmlentities($localCollection) ?></a></li>
	<?php endforeach ?>

</ul>

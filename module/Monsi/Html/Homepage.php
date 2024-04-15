<?php

use Module\Lipupini\Collection;

$localCollections = (new Collection\Utility($this->system))->allCollectionFolders();

require(__DIR__ . '/Core/Open.php') ?>

<h1>
	<?php echo $this->system->frontendModule . ' Frontend Module' ?>
</h1>

<?php

require(__DIR__ . '/Core/Close.php');

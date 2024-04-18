<?php

require(__DIR__ . '/Core/Open.php') ?>

<div class="centered-content">
	<div>
		<h1><?php echo $this->system->frontendModule ?> Module</h1>
		<p><a href="<?php echo $this->system->baseUri ?>@">View Collections</a></p>
	</div>
</div>

<?php

require(__DIR__ . '/Core/Close.php');

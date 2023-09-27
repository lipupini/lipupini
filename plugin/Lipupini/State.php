<?php

/*
This is a shared state throughout the application plugin lifecycle. It is initialized in a Plugin's `webroot/index.php`
and passed into each plugin which must return it into the plugin queue after optionally modifying it.

See `system/Lipupini.php`
*/

namespace Plugin\Lipupini;

class State {
	public function __construct(
		public string|null $lipupiniMethod = null, // What method to call next in `system/Lipupini.php` after plugin returns, e.g. `shutdown`
		public string|null $collectionFolderName = null, // Where the account identifier files are stored relative to `DIR_COLLECTION` in `system/Initialize.php
		public string|null $collectionUrl = null // URL where the collection is accessed e.g. from a web browser
	) { }
}

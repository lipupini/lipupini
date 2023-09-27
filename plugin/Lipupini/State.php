<?php

namespace Plugin\Lipupini;

class State {
	public function __construct(
		public string|null $lipupiniMethod = null, // What method to call next in `system/Liputini.php` after plugin returns, e.g. `shutdown`
		public string|null $collectionDirectory = null, // Where the account identifier files are stored relative to `DIR_COLLECTION` in `system/Initialize.php
		public string|null $collectionUrl = null // URL where the collection is accessed e.g. from a web browser
	) { }
}

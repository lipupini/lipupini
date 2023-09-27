<?php

/*
The Url plugin should be able to parse a request that starts with an "@".
For example: http://localhost/@example
An error will be thrown if there is not a corresponding directory in `collection`
After this URL is detected and validated, the collection directory is added
to State and available for subsequent plugins
*/

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class Url extends Plugin {
	public function start(State $state): State {
		if (preg_match('#^/@([^/?]*)#', $_SERVER['REQUEST_URI'], $matches)) {
			Lipupini::validateCollectionFolderName(collectionFolderName: $matches[1], disallowHostForLocal: true);
			$state->collectionFolderName = $matches[1];
			$state->collectionUrl = 'https://' . HOST . '/@' . $matches[1];
		}

		return $state;
	}
}

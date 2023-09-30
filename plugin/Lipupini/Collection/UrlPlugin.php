<?php

/*

After the UrlPlugin runs, the state is updated with information about the requested collection.
For example, if the request is: http://localhost/@example/Meme/cat-computer.jpg.html

$state->collectionFolderName === 'example`
$state->collectionUrl === http://localhost/@example
$state->collectionPath === 'Meme/cat-computer.jpg`

Subsequent queued plugins can then check for this data.

$state->collectionPath does not include the `.html` because it also refers to the path of the
actual image in the collection folder, not just the cached web path.

*/

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini\Collection;
use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;
use System\Plugin;

class UrlPlugin extends Plugin {
	public function start(State $state): State {
		if (!preg_match('#^/@([^/?]+)/?([^?]*)#', $_SERVER['REQUEST_URI'], $matches)) {
			return $state;
		}

		Collection\Utility::validateCollectionFolderName(collectionFolderName: $matches[1]);
		$state->collectionFolderName = $matches[1];
		$state->collectionUrl = 'https://' . HOST . '/@' . $matches[1];
		if (str_contains($matches[2], '../')) {
			throw new Exception('Invalid path');
		}
		$state->collectionPath = preg_replace('#\.html$#', '', $matches[2]);
		return $state;
	}
}

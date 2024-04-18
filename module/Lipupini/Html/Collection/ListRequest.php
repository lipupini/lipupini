<?php

namespace Module\Lipupini\Html\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class ListRequest extends Http {
	public array $collectionNames = [];

	public string $pageTitle = '@';
	public string $htmlHead = '<link rel="stylesheet" href="/css/CollectionList.css?v=' . FRONTEND_CACHE_VERSION . '">' . "\n";

	public function initialize(): void {
		// The URL path must be `/@` or `/@/`
		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@/?$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
			return;
		}

		$this->collectionNames = (new Collection\Utility($this->system))->allCollectionFolders();

		$this->renderHtml();
		$this->system->shutdown = true;
	}

	public function renderHtml(): void {
		ob_start();
		require($this->system->dirModule . '/' . $this->system->frontendModule . '/Html/Collection/List.php');
		$this->system->responseContent = ob_get_clean();
		$this->system->responseType = 'text/html';
	}
}

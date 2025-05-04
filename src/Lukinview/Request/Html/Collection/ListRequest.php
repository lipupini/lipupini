<?php

namespace Module\Lukinview\Request\Html\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request;

class ListRequest extends Request\Html {
	public array $collectionNames = [];

	public function initialize(): void {
		// The URL path must be `/@` or `/@/`
		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@/?$#', parse_url($_SERVER['REQUEST_URI_DECODED'], PHP_URL_PATH))) {
			return;
		}

		$this->pageTitle = '@';
		$this->collectionNames = (new Collection\Utility($this->system))->allCollectionFolders();
		$this->addStyle('/css/Global.css');
		$this->addStyle('/css/CollectionList.css');
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

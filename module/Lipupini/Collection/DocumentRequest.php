<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Http;

class DocumentRequest extends Http {
	public string|null $pageTitle = null;
	public string|null $htmlHead = null;
	private array|null $fileData = null;
	private string|null $parentPath = null;
	public string|null $collectionFileName = null;

	public function initialize(): void {
		if (empty($this->system->requests[Collection\Request::class]->folderName)) {
			return;
		}

		if (empty($this->system->requests[Collection\Request::class]->path)) {
			return;
		}

		// Only applies to, e.g. http://locahost/@example/memes/cat-computer.jpg.html
		// Does not apply to http://locahost/@example/memes/
		if (
			!pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION) ||
			!preg_match('#\.(?:[^.]+)\.html$#', $_SERVER['REQUEST_URI'])
		) {
			return;
		}

		// Every computer requesting collection HTML will need to explicitly accept "text/html"
		if (!$this->validateRequestMimeTypes('HTTP_ACCEPT', [
			'text/html',
		])) {
			return;
		}

		$this->renderHtml();
		$this->system->shutdown = true;
	}

	public function renderHtml(): void {
		$this->loadViewData();
		header('Content-type: text/html');
		ob_start();
		require($this->system->dirModule . '/' . $this->system->frontendView . '/Html/Collection/Document.php');
		$this->system->responseContent = ob_get_clean();
	}

	private function loadViewData(): void {
		$collectionFolderName = $this->system->requests[Collection\Request::class]->folderName;
		$collectionRequestPath = $this->system->requests[Collection\Request::class]->path;

		$this->pageTitle = $collectionRequestPath . '@' . $collectionFolderName . '@' . $this->system->host;
		$collectionData = (new Collection\Utility($this->system))->getCollectionData($collectionFolderName, $collectionRequestPath);

		$this->collectionFileName = preg_replace('#\.html$#', '', $collectionRequestPath);

		if (array_key_exists($this->collectionFileName, $collectionData)) {
			$this->fileData = $collectionData[$this->collectionFileName];
		} else {
			$this->fileData = [];
		}

		$parentFolder = dirname($collectionRequestPath);
		$this->parentPath = '@' . $collectionFolderName . ($parentFolder !== '.' ? '/' . $parentFolder : '');
		$this->htmlHead = '<link rel="stylesheet" href="/css/Document.css">' . "\n";
	}
}
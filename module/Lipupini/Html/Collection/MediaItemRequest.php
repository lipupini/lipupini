<?php

namespace Module\Lipupini\Html\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class MediaItemRequest extends Http {
	public string $pageTitle = '';
	public string $htmlHead = '';
	public string|null $pageImagePreviewUri = null;
	private array $fileData = [];
	private string|null $parentPath = null;
	public string|null $collectionFileName = null;
	public string|null $collectionName = null;
	public string|null $mediaType = null;

	public function initialize(): void {
		if (empty($this->system->request[Collection\Request::class]->name)) {
			return;
		}

		if (empty($this->system->request[Collection\Request::class]->file)) {
			return;
		}

		// Only applies to, e.g. http://locahost/@/example/memes/cat-computer.jpg.html
		// Does not apply to http://locahost/@/example/memes/
		if (
			!pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION) ||
			!preg_match('#\.(?:[^.]+)\.html$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
		) {
			return;
		}

		$this->collectionFileName = preg_replace('#\.html$#', '', $this->system->request[Collection\Request::class]->file);

		// Make sure file in collection exists before proceeding
		if (!file_exists($this->system->dirCollection . '/' . $this->system->request[Collection\Request::class]->name . '/' . $this->collectionFileName)) {
			return;
		}

		if (!$this->loadViewData()) {
			return;
		}

		$this->renderHtml();
		$this->system->shutdown = true;
	}

	public function renderHtml(): void {
		ob_start();
		require($this->system->dirModule . '/' . $this->system->frontendModule . '/Html/Collection/MediaItem.php');
		$this->system->responseContent = ob_get_clean();
		$this->system->responseType = 'text/html';
	}

	private function loadViewData(): bool {
		$this->collectionName = $this->system->request[Collection\Request::class]->name;
		$collectionFile = $this->system->request[Collection\Request::class]->file;

		$this->pageTitle = $collectionFile . '@' . $this->collectionName . '@' . $this->system->host;
		$collectionUtility = new Collection\Utility($this->system);

		// `$collectionFile` has a filename, we want to know what directory it's in
		$collectionFileDirname = pathinfo($collectionFile, PATHINFO_DIRNAME);
		$collectionFileDirname = $collectionFileDirname === '.' ? '' : $collectionFileDirname;
		$collectionData = $collectionUtility->getCollectionData($this->collectionName, $collectionFileDirname, true);
		if (array_key_exists($this->collectionFileName, $collectionData)) {
			$this->fileData = $collectionData[$this->collectionFileName];
		} else {
			$this->fileData = [];
		}

		if (($this->fileData['visibility'] ?? null) === 'hidden') {
			return false;
		}

		$this->mediaType = $collectionUtility->mediaTypesByExtension()[pathinfo($this->collectionFileName, PATHINFO_EXTENSION)]['mediaType'];

		if ($this->mediaType === 'image') {
			$this->pageImagePreviewUri = $this->system->staticMediaBaseUri . $this->collectionName . '/image/thumbnail/' . $this->collectionFileName;
		} else {
			$this->pageImagePreviewUri = $this->system->staticMediaBaseUri . $this->collectionName . '/thumbnail/' . $this->collectionFileName . '.png';
		}

		$parentFolder = dirname($collectionFile);
		$this->parentPath = '@/' . $this->collectionName . ($parentFolder !== '.' ? '/' . $parentFolder : '');
		if (!empty($_SERVER['HTTP_REFERER']) && preg_match('#' . preg_quote($this->parentPath) . '\?page=([0-9]+)$#', $_SERVER['HTTP_REFERER'], $matches)) {
			$this->parentPath .= '?page=' . $matches[1];
		}
		$this->htmlHead =
			'<link rel="stylesheet" href="/css/Document.css">' . "\n" .
			'<link rel="stylesheet" href="/css/MediaType/' . htmlentities(ucfirst($this->mediaType)) . '.css">' . "\n";

		return true;
	}
}

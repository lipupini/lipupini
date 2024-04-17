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

		$this->system->shutdown = true;

		$collectionHtmlFile = $this->system->request[Collection\Request::class]->file;

		// Only applies to, e.g. http://locahost/@example/memes/cat-computer.jpg.html
		// Does not apply to http://locahost/@example/memes/
		if (
			!pathinfo($collectionHtmlFile, PATHINFO_EXTENSION) ||
			!preg_match('#\.[^\.]+\.html$#', $collectionHtmlFile)
		) {
			return;
		}

		$this->collectionFileName = preg_replace('#\.html$#', '', $collectionHtmlFile);

		// Make sure file in collection exists before proceeding
		if (!file_exists($this->system->dirCollection . '/' . $this->system->request[Collection\Request::class]->name . '/' . $this->collectionFileName)) {
			return;
		}

		if (!$this->loadViewData()) {
			return;
		}

		$this->renderHtml();
	}

	public function renderHtml(): void {
		ob_start();
		require($this->system->dirModule . '/' . $this->system->frontendModule . '/Html/Collection/MediaItem.php');
		$this->system->responseContent = ob_get_clean();
		$this->system->responseType = 'text/html';
	}

	private function loadViewData(): bool {
		$this->collectionName = $this->system->request[Collection\Request::class]->name;

		$this->pageTitle = $this->collectionFileName . '@' . $this->collectionName . '@' . $this->system->host;
		$collectionUtility = new Collection\Utility($this->system);

		// `$this->collectionFileName` has a filename, we want to know what directory it's in
		$collectionFileDirname = pathinfo($this->collectionFileName, PATHINFO_DIRNAME);
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

		$parentFolder = dirname($this->collectionFileName);
		$this->parentPath = '@' . $this->collectionName . ($parentFolder !== '.' ? '/' . $parentFolder : '');
		if (!empty($_SERVER['HTTP_REFERER']) && preg_match('#' . preg_quote($this->parentPath) . '\?page=([0-9]+)$#', $_SERVER['HTTP_REFERER'], $matches)) {
			$this->parentPath .= '?page=' . $matches[1];
		}
		$this->htmlHead =
			'<link rel="stylesheet" href="/css/MediaItem.css?v=' . FRONTEND_CACHE_VERSION . '">' . "\n" .
			'<link rel="stylesheet" href="/css/MediaType/' . htmlentities(ucfirst($this->mediaType)) . '.css?v=' . FRONTEND_CACHE_VERSION . '">' . "\n";

		return true;
	}
}

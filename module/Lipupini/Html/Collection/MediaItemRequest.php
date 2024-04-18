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
	public string|null $collectionFilePath = null;
	public string|null $mediaType = null;

	use Collection\Trait\CollectionRequest;

	public function initialize(): void {
		// URLs start with `/@` (but must be followed by something and something other than `/` or `?`)
		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@(?!/|\?|$)#', $_SERVER['REQUEST_URI'])) return;
		// Media item HTML requests must have a `.html` extension
		if (pathinfo(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), PATHINFO_EXTENSION) !== 'html') return;

		$this->collectionNameFromSegment(1, '@');

		$this->collectionFilePath = rawurldecode(
			preg_replace('#\.html$#', '',
				preg_replace(
					'#^/@' . preg_quote($this->collectionName) . '/?#', '',
					parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
				)
			)
		);

		// Make sure file in collection exists before proceeding
		if (!file_exists($this->system->dirCollection . '/' . $this->collectionName . '/' . $this->collectionFilePath)) {
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
		$this->pageTitle = $this->collectionFilePath . '@' . $this->collectionName . '@' . $this->system->host;
		$collectionUtility = new Collection\Utility($this->system);
		// `$this->collectionFilePath` has a filename, we want to know what directory it's in
		$collectionFileDirname = pathinfo($this->collectionFilePath, PATHINFO_DIRNAME);
		$collectionFileDirname = $collectionFileDirname === '.' ? '' : $collectionFileDirname;
		$collectionData = $collectionUtility->getCollectionData($this->collectionName, $collectionFileDirname, true);
		if (array_key_exists($this->collectionFilePath, $collectionData)) {
			$this->fileData = $collectionData[$this->collectionFilePath];
		} else {
			$this->fileData = [];
		}

		if (($this->fileData['visibility'] ?? null) === 'hidden') {
			return false;
		}

		$this->mediaType = $collectionUtility->mediaTypesByExtension()[pathinfo($this->collectionFilePath, PATHINFO_EXTENSION)]['mediaType'];

		if ($this->mediaType === 'image') {
			$this->pageImagePreviewUri = $this->system->staticMediaBaseUri . $this->collectionName . '/image/thumbnail/' . $this->collectionFilePath;
		} else {
			$this->pageImagePreviewUri = $this->system->staticMediaBaseUri . $this->collectionName . '/thumbnail/' . $this->collectionFilePath . '.png';
		}

		$parentFolder = dirname($this->collectionFilePath);
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

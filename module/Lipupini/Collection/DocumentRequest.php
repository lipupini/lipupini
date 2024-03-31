<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class DocumentRequest extends Http {
	public string $pageTitle = '';
	public string|null $htmlHead = null;
	public string|null $pageImagePreviewUri = null;
	private array $fileData = [];
	private string|null $parentPath = null;
	public string|null $collectionFileName = null;

	public function initialize(): void {
		if (empty($this->system->request[Collection\Request::class]->folderName)) {
			return;
		}

		if (empty($this->system->request[Collection\Request::class]->path)) {
			return;
		}

		// Only applies to, e.g. http://locahost/@example/memes/cat-computer.jpg.html
		// Does not apply to http://locahost/@example/memes/
		if (
			!pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION) ||
			!preg_match('#\.(?:[^.]+)\.html$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
		) {
			return;
		}

		$this->collectionFileName = preg_replace('#\.html$#', '', $this->system->request[Collection\Request::class]->path);

		// Make sure file in collection exists before proceeding
		if (!file_exists($this->system->dirCollection . '/' . $this->system->request[Collection\Request::class]->folderName . '/' . urldecode($this->collectionFileName))) {
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
		require($this->system->dirModule . '/' . $this->system->frontendModule . '/Html/Collection/Document.php');
		$this->system->responseContent = ob_get_clean();
		$this->system->responseType = 'text/html';
	}

	private function loadViewData(): bool {
		$collectionFolderName = $this->system->request[Collection\Request::class]->folderName;
		$collectionRequestPath = $this->system->request[Collection\Request::class]->path;

		$this->pageTitle = rawurldecode($collectionRequestPath . '@' . $collectionFolderName) . '@' . $this->system->host;
		$collectionUtility = new Collection\Utility($this->system);

		// `$collectionRequestPath` has a filename, we want to know what directory it's in
		$collectionRequestPathFolder = pathinfo($collectionRequestPath, PATHINFO_DIRNAME);
		$collectionRequestPathFolder = $collectionRequestPathFolder === '.' ? '' : $collectionRequestPathFolder;
		$collectionData = $collectionUtility->getCollectionData($collectionFolderName, $collectionRequestPathFolder, true);
		if (array_key_exists($this->collectionFileName, $collectionData)) {
			$this->fileData = $collectionData[$this->collectionFileName];
		} else {
			$this->fileData = [];
		}

		if (($this->fileData['visibility'] ?? null) === 'hidden') {
			return false;
		}

		$isImageFile = $collectionUtility->mediaTypesByExtension()[pathinfo($this->collectionFileName, PATHINFO_EXTENSION)]['mediaType'] === 'image';

		if ($isImageFile) {
			$this->pageImagePreviewUri = $this->system->staticMediaBaseUri . $collectionFolderName . '/image/thumbnail/' . $this->collectionFileName;
		} else {
			$this->pageImagePreviewUri = $this->system->staticMediaBaseUri . $collectionFolderName . '/thumbnail/' . $this->collectionFileName . '.png';
		}

		$parentFolder = dirname($collectionRequestPath);
		$this->parentPath = '@' . $collectionFolderName . ($parentFolder !== '.' ? '/' . $parentFolder : '');
		$this->htmlHead = '<link rel="stylesheet" href="/css/Document.css">' . "\n";

		return true;
	}
}

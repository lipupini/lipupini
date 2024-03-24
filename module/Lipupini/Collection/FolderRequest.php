<?php

namespace Module\Lipupini\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class FolderRequest extends Http {
	public array $collectionData = [];

	public int $perPage = 36;

	protected string|null $nextUrl = null;
	protected string|null $prevUrl = null;

	use Collection\Trait\HasPaginatedCollectionData;

	public string $pageTitle = '';
	public string|null $htmlHead = null;
	public array $fileTypes = [];

	public string|null $collectionFolderName = null;
	public string|null $collectionRequestPath = null;

	public function initialize(): void {
		if (empty($this->system->requests[Collection\Request::class]->folderName)) {
			return;
		}

		$this->collectionFolderName = $this->system->requests[Collection\Request::class]->folderName;
		$this->collectionRequestPath = $this->system->requests[Collection\Request::class]->path;

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if (pathinfo($this->collectionRequestPath, PATHINFO_EXTENSION)) {
			return;
		} else if (!is_dir($this->system->dirCollection . '/' . $this->collectionFolderName . '/' . $this->collectionRequestPath)) {
			return;
		}

		$this->renderHtml();
		$this->system->shutdown = true;
	}

	public function renderHtml(): void {
		$this->loadViewData();
		header('Content-type: text/html');
		ob_start();
		require($this->system->dirModule . '/' . $this->system->frontendModule . '/Html/Collection/Folder.php');
		$this->system->responseContent = ob_get_clean();
	}

	private function loadViewData(): void {
		$this->collectionData = (new Collection\Utility($this->system))->getCollectionData($this->collectionFolderName, $this->collectionRequestPath);

		$this->loadPaginationAttributes();

		if ($this->collectionRequestPath) {
			$this->pageTitle = $this->collectionRequestPath . '@' . $this->collectionFolderName . '@' . $this->system->host;
			$this->parentPath = '@' . $this->collectionFolderName;
			$exploded = explode('/', $this->collectionRequestPath);
			if (count($exploded) >= 2) {
				$this->parentPath .= '/' . implode('/', array_slice($exploded, 0, -1));
			}
		} else {
			$this->pageTitle = '@' . $this->collectionFolderName . '@' . $this->system->host;
			$this->parentPath = '';
		}

		$webPath = '/@' . $this->collectionFolderName . ($this->collectionRequestPath ? '/' . $this->collectionRequestPath : '');

		if ($this->page < $this->numPages) {
			$query['page'] = $this->page + 1;
			$this->nextUrl = $webPath . '?' . http_build_query($query);
		} else {
			$this->nextUrl = false;
		}
		if ($this->page === 2) {
			$this->prevUrl = $webPath;
		} else if ($this->page > 2) {
			$query['page'] = $this->page - 1;
			$this->prevUrl = $webPath . '?' . http_build_query($query);
		} else {
			$this->prevUrl = false;
		}

		$this->fileTypes = [
			'Video' => MediaProcessor\VideoRequest::mimeTypes(),
			'Audio' => MediaProcessor\AudioRequest::mimeTypes(),
			'Image' => MediaProcessor\ImageRequest::mimeTypes(),
			'Text' => MediaProcessor\TextRequest::mimeTypes(),
		];

		$this->htmlHead = '<link rel="stylesheet" href="/css/Folder.css">' . "\n"
			. '<link rel="alternate" type="application/rss+xml" title="'
				. htmlentities($this->collectionFolderName .  '@' . $this->system->host) . '" href="'
				. htmlentities($this->system->baseUri . '@' . $this->collectionFolderName . '?feed=rss')
			. '">' . "\n";
	}
}

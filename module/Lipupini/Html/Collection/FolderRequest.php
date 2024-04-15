<?php

namespace Module\Lipupini\Html\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Request\Incoming\Http;

class FolderRequest extends Http {
	public array $collectionData = [];

	protected string|null $nextUrl = null;
	protected string|null $prevUrl = null;

	use Collection\Trait\HasPaginatedCollectionData;

	public string $pageTitle = '';
	public string|null $pageImagePreviewUri = null;
	public string $htmlHead = '';

	public string $collectionName = '';
	public string $collectionFolder = '';

	public function initialize(): void {
		// URLs start with `/@`
		if (!str_starts_with($_SERVER['REQUEST_URI'], '/@')) {
			return;
		}

		// Must have a collection name populated to proceed
		if (empty($this->system->request[Collection\Request::class]->name)) {
			return;
		}

		$this->collectionName = $this->system->request[Collection\Request::class]->name;

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if (pathinfo($this->collectionFolder, PATHINFO_EXTENSION)) {
			return;
		} else if (!is_dir($this->system->dirCollection . '/' . $this->collectionName . '/' . $this->collectionFolder)) {
			return;
		}

		$folder = $this->system->request[Collection\Request::class]->folder;
		(new Collection\Utility($this->system))->validateCollectionFolder($this->collectionName, $folder);
		$this->collectionFolder = $folder;

		$this->renderHtml();
		$this->system->shutdown = true;
	}

	public function renderHtml(): void {
		$this->loadViewData();
		ob_start();
		require($this->system->dirModule . '/' . $this->system->frontendModule . '/Html/Collection/Folder.php');
		$this->system->responseContent = ob_get_clean();
		$this->system->responseType = 'text/html';
	}

	private function loadViewData(): void {
		$this->collectionData = (new Collection\Utility($this->system))->getCollectionData($this->collectionName, $this->collectionFolder);

		$this->loadPaginationAttributes();

		if ($this->collectionFolder) {
			$this->pageTitle = $this->collectionFolder . '@' . $this->collectionName . '@' . $this->system->host;
			$this->parentPath = '@' . $this->collectionName;
			$exploded = explode('/', $this->collectionFolder);
			if (count($exploded) >= 2) {
				$this->parentPath .= '/' . implode('/', array_slice($exploded, 0, -1));
			}
		} else {
			$this->pageTitle = '@' . $this->collectionName . '@' . $this->system->host;
			$this->parentPath = '@';
		}

		$webPath = '/@' . $this->collectionName . ($this->collectionFolder ? '/' . $this->collectionFolder : '');

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

		$avatarUrlPath = Collection\MediaProcessor\Avatar::avatarUrlPath($this->system, $this->collectionName);
		$this->pageImagePreviewUri = $avatarUrlPath ?? null;

		$this->htmlHead .= '<link rel="stylesheet" href="/css/Folder.css">' . "\n";
		foreach (array_keys($this->system->mediaType) as $mediaType) {
			$this->htmlHead .= '<link rel="stylesheet" href="/css/MediaType/' . htmlentities(ucfirst($mediaType)) . '.css">' . "\n";
		}
		$this->htmlHead .= '<link rel="alternate" type="application/rss+xml" title="'
				. htmlentities($this->collectionName .  '@' . $this->system->host) . '" href="'
				. htmlentities($this->system->baseUri . 'rss/' . $this->collectionName . '/' . $this->collectionName . '-feed.rss')
			. '">' . "\n";
	}
}

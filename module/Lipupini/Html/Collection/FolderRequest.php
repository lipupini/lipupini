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

	public string $collectionFolder = '';

	use Collection\Trait\CollectionRequest;

	public function initialize(): void {
		// URLs start with `/@` (but must be followed by something and something other than `/` or `?`)
		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@(?!/|$)#', $_SERVER['REQUEST_URI'])) return;
		// To be considered a folder request, there must not be an extension
		if (pathinfo(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), PATHINFO_EXTENSION)) return;

		$this->collectionNameFromSegment(1, '@');

		$this->collectionFolder = preg_replace(
			'#^/@' . preg_quote($this->collectionName) . '/?#', '',
			parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
		);

		(new Collection\Utility($this->system))->validateCollectionFolder($this->collectionName, $this->collectionFolder);

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

		$this->htmlHead .= '<link rel="stylesheet" href="/css/Folder.css?v=' . FRONTEND_CACHE_VERSION . '">' . "\n";
		foreach (array_keys($this->system->mediaType) as $mediaType) {
			$this->htmlHead .= '<link rel="stylesheet" href="/css/MediaType/' . htmlentities(ucfirst($mediaType)) . '.css?v=' . FRONTEND_CACHE_VERSION . '">' . "\n";
		}
		$this->htmlHead .= '<link rel="alternate" type="application/rss+xml" title="'
				. htmlentities($this->collectionName .  '@' . $this->system->host) . '" href="'
				. htmlentities($this->system->baseUri . 'rss/' . $this->collectionName . '/' . $this->collectionName . '-feed.rss')
			. '">' . "\n";
	}
}

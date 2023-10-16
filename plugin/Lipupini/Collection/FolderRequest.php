<?php

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class FolderRequest extends Lipupini\Http\Request {
	public string|null $collectionFolderName = null;
	public string|null $collectionRequestPath = null;
	public array $collectionData = [];

	public int $perPage = 36;

	protected string|null $nextUrl = null;
	protected string|null $prevUrl = null;

	use Collection\Trait\HasPaginatedCollectionData;

	public string $pageTitle = '';
	public string|null $htmlHead = null;

	public function initialize(): void {
		$collectionFolderName = $this->getCollectionFolderNameFromRequest();

		if ($collectionFolderName === false) {
			return;
		}

		$this->collectionFolderName = $collectionFolderName;

		// Every computer requesting collection HTML will need to explicitly accept "text/html"
		if (!$this->validateRequestMimeTypes('HTTP_ACCEPT', [
			'text/html',
		]) || !empty($_GET['feed'])) {
			return;
		}

		$this->collectionRequestPath = $this->getCollectionRequestPath();

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if (pathinfo($this->collectionRequestPath, PATHINFO_EXTENSION)) {
			return;
		} else if (!is_dir($this->system->dirCollection . '/' . $collectionFolderName . '/' . $this->collectionRequestPath)) {
			return;
		}

		$this->renderHtml();
		$this->system->shutdown = true;
	}

	public function renderHtml(): void {
		$this->loadViewData();
		header('Content-type: text/html');
		ob_start();
		require($this->system->dirPlugin . '/' . $this->system->frontendView . '/Html/Collection/Folder.php');
		$this->system->responseContent = ob_get_clean();
	}

	protected function getCollectionRequestPath() {
		return parse_url(
			preg_replace('#^/@' . $this->collectionFolderName . '/?#', '', $_SERVER['REQUEST_URI']),
			PHP_URL_PATH
		) ?? '';
	}

	protected function getCollectionFolderNameFromRequest() {
		if (!str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '@')) {
			return false;
		}

		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@([^/?]+)' . '#', $_SERVER['REQUEST_URI'], $matches)) {
			return false;
		}

		if (str_contains($_SERVER['REQUEST_URI'], '..')) {
			throw new Exception('Suspicious collection URL');
		}

		$collectionFolderName = $matches[1];

		if (!$collectionFolderName || strlen($collectionFolderName) > 200) {
			throw new Exception('Suspicious collection identifier (E1)');
		}

		if (substr_count($collectionFolderName, '@')) {
			throw new Exception('Suspicious collection identifier (E2)');
		}

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		return $collectionFolderName;
	}

	private function loadViewData(): void {
		if (empty($_GET['search'])) {
			$this->collectionData = (new Collection\Utility($this->system))->getCollectionData($this->collectionFolderName, $this->collectionRequestPath);
		} else {
			if ($this->collectionRequestPath) {
				throw new Collection\Exception('Trying to search when not at collection root');
			}
			$this->collectionData = (new Collection\Utility($this->system))->getSearchData($_GET['search']);
		}

		$this->loadPaginationAttributes();

		if ($this->collectionRequestPath) {
			$this->pageTitle = $this->collectionRequestPath . '@' . $this->collectionFolderName . '@' . $this->system->host;
			$this->parentPath = '@' . $this->collectionFolderName;
			$exploded = explode('/', $this->collectionRequestPath);
			if (count($exploded) > 2) {
				$this->parentPath .= '/' . implode('/', array_slice($exploded, 0, -1));
			}
		} else {
			$this->pageTitle = '@' . $this->collectionFolderName . '@' . $this->system->host;
			if (!empty($_GET['search'])) {
				$this->pageTitle = $_GET['search'] . $this->pageTitle;
				$this->parentPath = '@' . $this->collectionFolderName;
			} else {
				$this->parentPath = '';
			}
		}

		$webPath = '/@' . $this->collectionFolderName . ($this->collectionRequestPath ? '/' . $this->collectionRequestPath : '');

		if ($this->page < $this->numPages) {
			if (!empty($_GET['search'])) {
				$query['search'] = $_GET['search'];
			}
			$query['page'] = $this->page + 1;
			$this->nextUrl = $webPath . '?' . http_build_query($query);
		} else {
			$this->nextUrl = 'javascript:void(0)';
		}
		if ($this->page === 2) {
			if (empty($_GET['search'])) {
				$this->prevUrl = $webPath;
			} else {
				$this->prevUrl = $webPath . '?search=' . $_GET['search'];
			}
		} else if ($this->page > 2) {
			if (!empty($_GET['search'])) {
				$query['search'] = $_GET['search'];
			}
			$query['page'] = $this->page - 1;
			$this->prevUrl = $webPath . '?' . http_build_query($query);
		} else {
			$this->prevUrl = 'javascript:void(0)';
		}

		$this->htmlHead = '<link rel="stylesheet" href="/css/Folder.css">' . "\n";
	}
}

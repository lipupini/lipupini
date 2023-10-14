<?php

namespace Plugin\Lipupini\Collection;

use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class FolderRequest extends Lipupini\Http\Request {
	public string|null $collectionFolderName = null;
	public string|null $collectionRequestPath = null;

	public int $perPage = 36;

	private int|null $total = null;
	private int|null $page = null;
	private string|null $nextUrl = null;
	private string|null $prevUrl = null;
	private int|null $numPages = null;
	private array|null $collectionData = null;
	private string|null $parentPath = null;

	public string $pageTitle = 'Testing';
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
		])) {
			return;
		}

		$this->collectionRequestPath = preg_replace('#^/@' . $this->collectionFolderName . '/?#', '', $_SERVER['REQUEST_URI']);

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if (pathinfo($this->collectionRequestPath, PATHINFO_EXTENSION)) {
			return;
		}

		$this->renderHtml();
		$this->system->shutdown = true;
	}

	public function renderHtml(): void {
		$this->loadViewData();
		header('Content-type: text/html');
		require($this->system->dirPlugin . '/' . $this->system->frontendView . '/Html/Collection/Folder.php');
	}

	protected function getCollectionFolderNameFromRequest() {
		if (!str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '@')) {
			return false;
		}

		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@([^/?]+)' . '#', $_SERVER['REQUEST_URI'], $matches)) {
			return false;
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
			$data = (new Collection\Utility($this->system))->getCollectionData($this->collectionFolderName, $this->collectionRequestPath);
		} else {
			if ($this->collectionRequestPath) {
				throw new Exception('Trying to search when not at collection root');
			}
			$data = (new Collection\Utility($this->system))->getSearchData($_GET['search']);
		}

		$this->page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
		$this->total = count( $data);
		$this->numPages = ceil($this->total / $this->perPage);

		if ($this->collectionRequestPath) {
			$this->pageTitle = $this->collectionRequestPath . '@' . $this->collectionFolderName . '@' . $this->system->host;
			$this->parentPath = '@' . $this->collectionFolderName;
			$exploded = explode('/', $this->collectionRequestPath);
			if (count($exploded) > 1) {
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

		if ($this->page > $this->numPages) {
			throw new Exception('Invalid page number');
		}

		$this->collectionData = array_slice($data, ($this->page - 1) * $this->perPage, $this->perPage);

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

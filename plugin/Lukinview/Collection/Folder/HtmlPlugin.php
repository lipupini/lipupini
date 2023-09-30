<?php

namespace Plugin\Lukinview\Collection\Folder;

use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class HtmlPlugin extends Plugin {
	public string|null $pageTitle = null;

	public int $perPage = 36;

	private int|null $total = null;
	private int|null $page = null;
	private string|null $nextUrl = null;
	private string|null $prevUrl = null;
	private int|null $numPages = null;
	private array|null $collectionData = null;
	private string|null $collectionFolderName = null;
	private string|null $parentPath = null;

	public function start(State $state): State {
		if (empty($state->collectionFolderName)) {
			return $state;
		}

		if (!Lipupini::getClientAccept('HTML')) {
			return $state;
		}

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if (pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION)) {
			return $state;
		}

		$state->lipupiniMethod = 'shutdown';
		header('Content-type: text/html');
		return $this->renderHtml($state);
	}

	public function renderHtml(State $state) {
		$this->loadViewData($state);

		require(__DIR__ . '/../../Html/Core/Open.php');
		require(__DIR__ . '/Html/Grid.php');
		require(__DIR__ . '/../../Html/Core/Close.php');

		return $state;
	}

	private function loadViewData(State $state): void {
		if (empty($_GET['search'])) {
			$data = Lipupini::getCollectionData($state);
		} else {
			if ($state->collectionPath) {
				throw new Exception('Trying to search when not at collection root');
			}
			$data = Lipupini::getSearchData($state, $_GET['search']);
		}

		$this->page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
		$this->total = count( $data);
		$this->numPages = ceil($this->total / $this->perPage);

		if ($state->collectionPath) {
			$this->pageTitle = $state->collectionPath . '@' . $state->collectionFolderName . '@' . HOST;
			$this->parentPath = '@' . $state->collectionFolderName;
			$exploded = explode('/', $state->collectionPath);
			if (count($exploded) > 1) {
				$this->parentPath .= '/' . implode('/', array_slice($exploded, 0, -1));
			}
		} else {
			$this->pageTitle = '@' . $state->collectionFolderName . '@' . HOST;
			if (!empty($_GET['search'])) {
				$this->pageTitle = $_GET['search'] . $this->pageTitle;
				$this->parentPath = '@' . $state->collectionFolderName;
			} else {
				$this->parentPath = '';
			}
		}

		if ($this->page > $this->numPages) {
			throw new Exception('Invalid page number');
		}

		$this->collectionFolderName = $state->collectionFolderName;
		$this->collectionData = array_slice($data, ($this->page - 1) * $this->perPage, $this->perPage);

		$webPath = '/@' . $state->collectionFolderName . ($state->collectionPath ? '/' . $state->collectionPath : '');

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
	}
}

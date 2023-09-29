<?php

namespace Plugin\Lukinview\Collection;

use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class HtmlPlugin extends Plugin {
	public int $perPage = 36;

	private int|null $total = null;
	private int|null $page = null;
	private string|null $nextUrl = null;
	private string|null $prevUrl = null;
	private int|null $numPages = null;
	private array|null $collectionData = null;

	public function start(State $state): State {
		if (empty($state->collectionFolderName)) {
			return $state;
		}

		if (!Lipupini::getClientAccept('HTML')) {
			return $state;
		}

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if ($state->collectionPath !== '') {
			return $state;
		}

		$state->lipupiniMethod = 'shutdown';
		header('Content-type: text/html');
		return $this->renderHtml($state);
	}

	public function renderHtml(State $state) {
		$this->loadViewData($state->collectionFolderName);

		require(__DIR__ . '/../Html/Core/Open.php');
		require(__DIR__ . '/Html/Grid.php');
		require(__DIR__ . '/../Html/Core/Close.php');

		return $state;
	}

	private function loadViewData($collectionFolderName): void {
		$collectionData = Lipupini::getCollectionData($collectionFolderName);

		$this->page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
		$this->total = count( $collectionData );
		$this->numPages = ceil( $this->total / $this->perPage );

		if ($this->page > $this->numPages) {
			throw new Exception('Invalid page number');
		}

		$this->collectionData = array_slice( $collectionData, ($this->page - 1) * $this->perPage, $this->perPage );

		if ($this->page < $this->numPages) {
			$this->nextUrl = '/@' . $collectionFolderName . '?page=' . $this->page + 1;
		} else {
			$this->nextUrl = 'javascript:return false';
		}
		if ($this->page === 2) {
			$this->prevUrl = '/@' . $collectionFolderName;
		} else if ($this->page > 2) {
			$this->prevUrl = '/@' . $collectionFolderName . '?page=' . $this->page - 1;
		} else {
			$this->prevUrl = 'javascript:return false';
		}
	}
}

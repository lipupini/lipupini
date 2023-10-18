<?php

namespace Module\Lipupini\Collection\Trait;

use Module\Lipupini\Collection;

trait HasPaginatedCollectionData {
	protected int|null $total = null;
	protected int|null $page = null;
	protected int|null $numPages = null;
	protected string|null $parentPath = null;

	protected function loadPaginationAttributes(): void {
		if (empty($_GET['page'])) {
			$this->page = 1;
		} else if (!(int)$_GET['page'] || (int)$_GET['page'] < 1) {
			throw new Collection\Exception('Could not determine page');
		} else {
			$this->page = $_GET['page'];
		}

		$this->total = count($this->collectionData);
		$this->numPages = ceil($this->total / $this->perPage);

		if ($this->page > $this->numPages) {
			if ($this->page !== 1) {
				throw new Collection\Exception('Invalid page number');
			} else {
				// If we get here, there's only one page -- we don't need to slice the collection array
				return;
			}
		}

		$this->collectionData = array_slice($this->collectionData, ($this->page - 1) * $this->perPage, $this->perPage);
	}
}

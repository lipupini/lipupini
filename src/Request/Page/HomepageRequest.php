<?php

namespace Lipupini\Request\Page;

use Lipupini\Request;

class HomepageRequest extends Request\Html {
	public function initialize(): void  {
		if (parse_url($_SERVER['REQUEST_URI_DECODED'], PHP_URL_PATH) !== $this->system->baseUriPath) {
			return;
		}
		$this->pageTitle = 'Homepage@' . $this->system->host;
		$this->addStyle('/css/Global.css');
		$this->renderHtml();
		$this->system->responseType = 'text/html';
		$this->system->shutdown = true;
	}

	public function renderHtml(): void {
		ob_start();
		require(__DIR__ . '/Homepage.phtml');
		$this->system->responseContent = ob_get_clean();
	}
}

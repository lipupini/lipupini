<?php

namespace Module\Lukinview\Request\Html;

use Module\Lipupini\Request;

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
		header('Content-type: text/html');
		require(__DIR__ . '/../../Html/Homepage.php');
		$this->system->responseContent = ob_get_clean();
	}
}

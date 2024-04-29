<?php

namespace Module\Lipupini\Request;

use Module\Lipupini\Collection\Utility;

abstract class Html extends Queued {
	public string $pageTitle = '';
	public string $htmlHead = '';
	public string $htmlFoot = '';

	public function addScript(string $src) {
		$encodedSrc = Utility::urlEncodeUrl($src);
		if (function_exists('headers_send')) {
			header('Link: <' . $encodedSrc . '?v=' . FRONTEND_CACHE_VERSION . '>; rel=preload; as=script', false);
		}
		$this->htmlHead .= '<link href="' . $encodedSrc . '?v=' . FRONTEND_CACHE_VERSION . '" rel="preload" as="script">' . "\n";
		$this->htmlFoot .= '<script src="' . $encodedSrc . '?v=' . FRONTEND_CACHE_VERSION . '" defer></script>' . "\n";
	}

	public function addStyle(string $src) {
		$encodedSrc = Utility::urlEncodeUrl($src);
		if (function_exists('headers_send')) {
			header('Link: <' . $encodedSrc . '?v=' . FRONTEND_CACHE_VERSION . '>; rel=preload; as=style', false);
		}
		$this->htmlHead .= '<link href="' . $encodedSrc . '?v=' . FRONTEND_CACHE_VERSION . '" rel="stylesheet" type="text/css">' . "\n";
	}

	public function preloadMedia(string $src, string $type) {
		$encodedSrc = Utility::urlEncodeUrl($src);
		if (function_exists('headers_send')) {
			header('Link: <' . $encodedSrc . '>; rel=preload; as=' . $type, false);
		}
		$this->htmlHead .= '<link href="' . $encodedSrc . '" rel="preload" as="' . $type . '">' . "\n";
	}

	public function preloadReady() {
		if (function_exists('headers_send')) {
			headers_send(103);
		}
	}
}

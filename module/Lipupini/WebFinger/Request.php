<?php

namespace Module\Lipupini\WebFinger;

use Module\Lipupini\Request\Incoming\Http;

class Request extends Http {
	public static string $mimeType = 'application/jrd+json';

	public function initialize(): void {
		// https://webconcepts.info/concepts/well-known-uri/host-meta
		if (str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '.well-known/host-meta')) {
			$this->system->responseType = 'application/xrd+xml';
			$this->system->responseContent ='<?xml version="1.0" encoding="UTF-8"?>'
				. '<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">'
				. '<Link rel="lrdd" template="' . $this->system->baseUri . '.well-known/webfinger?resource={uri}"/>'
				. '</XRD>';
			return;
		}

		$webFingerAccount = $this->getWebFingerAccountFromRequest();

		if ($webFingerAccount === false) {
			return;
		}

		$exploded = explode('@', $webFingerAccount);
		$user = $exploded[0];
		$host = $exploded[1];

		if ($host !== $this->system->host) {
			throw new Exception('Hostname for WebFinger must correspond to current hostname');
		}

		$jsonData = [
			'subject' => 'acct:' . $webFingerAccount,
			'aliases' => [
				$this->system->baseUri . '@' . $user,
			],
			'links' => [
				[
					'rel' => 'http://webfinger.net/rel/profile-page',
					'type' => 'text/html',
					'href' => $this->system->baseUri . '@' . $user,
				],
				[
					'rel' => 'http://schemas.google.com/g/2010#updates-from',
					'type' => 'application/rss+xml',
					'href' => $this->system->baseUri . '@' . $user . '?feed=rss',
				],
				[
					'rel' => 'self',
					'type' => 'application/activity+json',
					'href' => $this->system->baseUri . '@' . $user . '?ap=profile',
				],
				[
					'rel' => 'http://webfinger.net/rel/avatar',
					'type' => 'image/png',
					'href' => $this->system->baseUri . 'c/' . $webFingerAccount . '/avatar.png',
				],
			]
		];

		$this->system->responseType = static::$mimeType;
		$this->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
		$this->system->shutdown = true;
	}

	protected function getWebFingerAccountFromRequest() {
		if (!str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '.well-known/webfinger')) {
			return false;
		}

		if (empty($_GET['resource'])) {
			throw new Exception('Could not find webfinger resource');
		}

		// May need to provide URL encoded alternative in addition to colon
		if (!str_starts_with($_GET['resource'], 'acct:')) {
			throw new Exception('Expected request for acct resource');
		}

		$webFingerAccount = preg_replace('#^acct:#', '', $_GET['resource']);

		// WebFinger request could be URL encoded, but it should contain "@"
		if (!str_contains($webFingerAccount, '@')) {
			$webFingerAccount = urldecode($webFingerAccount);
		}

		if ($webFingerAccount[0] === '@') {
			$webFingerAccount = preg_replace('#^@#', '', $webFingerAccount);
		}

		if (substr_count($webFingerAccount, '@') !== 1) {
			throw new Exception('Suspicious account format');
		}

		if (!$webFingerAccount) {
			throw new Exception('Could not determine WebFinger account');
		}

		return $webFingerAccount;
	}
}

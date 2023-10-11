<?php

namespace Plugin\Lipupini\WebFinger;

use Plugin\Lipupini;
use ActivityPhp;

class Request extends Lipupini\Http\Request {
	public string $responseType = 'application/jrd+json';

	public function initialize() {
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
					'type' => 'application/atom+xml',
					'href' => $this->system->baseUri . '@' . $user . '.atom',
				],
				[
					'rel' => 'self',
					'type' => 'application/activity+json',
					'href' => $this->system->baseUri . '@' . $user,
				],
				[
					'rel' => 'http://webfinger.net/rel/avatar',
					'type' => 'image/png',
					'href' => $this->system->baseUri . 'c/avatar/' . $webFingerAccount . '.png',
				],
			]
		];

		header('Content-type: ' . $this->responseType);
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

		$this->system->shutdown = true;
	}

	protected function getWebFingerAccountFromRequest() {
		if (!str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '.well-known/webfinger')) {
			return false;
		}

		if (!$this->clientAcceptsMimeTypes([
			'application/activity+json',
			'application/jrd+json',
			'application/json',
			$this->system->debug ? 'text/html' : null,
		])) {
			throw new Exception('Invalid request type');
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

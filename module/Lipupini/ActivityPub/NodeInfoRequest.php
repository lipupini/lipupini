<?php

namespace Module\Lipupini\ActivityPub;

use Module\Lipupini\Request\Http;

// https://github.com/jhass/nodeinfo/blob/main/PROTOCOL.md

class NodeInfoRequest extends Http {
	public function initialize(): void {
		if (!str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '.well-known/nodeinfo')) {
			return;
		}

		// Uncomment this to enforce request type
		/*if (!$this->validateRequestMimeTypes('HTTP_ACCEPT', [
			'application/json',
		])) {
			throw new Exception('Invalid request type');
		}*/

		if (isset($_GET['local'])) {
			$this->local();
		} else {
			$this->index();
		}

		$this->system->shutdown = true;
	}

	public function index() {
		$jsonData = [
			'links' => [
				[
					'rel' => 'http://nodeinfo.diaspora.software/ns/schema/2.0',
					'href' => $this->system->baseUri . '.well-known/nodeinfo?local',
				]
			],
		];

		header('Content-type: application/json');
		$this->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function local() {
		$jsonData = [
			'version' => '2.0',
			'software' => [
				'name' => 'lipupini',
				'version' => '69.420',
			],
			'protocols' => [
				'activitypub',
				'rss',
			],
			'openRegistrations' => 'false',
			'usage' => [
				'users' => [
					'total' => 1,
					'activeHalfYear' => 1,
					'activeMonth' => 1,
				],
				'localPosts' => 420,
				'localComments' => 69,
			],
			'services' => [
				'outbound' => [],
				'inbound' => [],
			],
			'metadata' => [
				'nodeName' => $this->system->host,
				'software' => [
					'homepage' => 'https://github.com/instalution/lipupini',
					'repository' => 'https://github.com/instalution/lipupini',
				],
			]
		];

		header('Content-type: application/json');
		$this->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}

<?php

namespace Module\Lipupini\ActivityPub;

use Module\Lipupini\Request\Queued;
use const Module\Lipupini\LIPUPINI_VERSION;

// https://github.com/jhass/nodeinfo/blob/main/PROTOCOL.md

class NodeInfoRequest extends Queued {
	public function initialize(): void {
		if (!str_starts_with($_SERVER['REQUEST_URI_DECODED'], $this->system->baseUriPath . '.well-known/nodeinfo')) {
			return;
		}

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

		$this->system->responseType = 'application/json';
		$this->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function local() {
		$jsonData = [
			'version' => '2.0',
			'software' => [
				'name' => 'lipupini',
				'version' => LIPUPINI_VERSION,
			],
			'protocols' => [
				'activitypub',
				'rss',
			],
			'openRegistrations' => 'false',
			// Lipupini itself does not record usage data, so random numbers are used
			// If you really want, you can parse logs and cache them in `/tmp`
			'usage' => [
				'users' => [
					'total' => rand(69, 420),
					'activeHalfYear' => rand(69, 420),
					'activeMonth' => rand(69, 420),
				],
				'localPosts' => rand(69, 420),
				'localComments' => rand(69, 420),
			],
			'services' => [
				'outbound' => [],
				'inbound' => [],
			],
			'metadata' => [
				'nodeName' => $this->system->host,
				'software' => [
					'homepage' => 'https://github.com/lipupini/lipupini',
					'repository' => 'https://github.com/lipupini/lipupini',
				],
			]
		];

		$this->system->responseType = 'application/json';
		$this->system->responseContent = json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}

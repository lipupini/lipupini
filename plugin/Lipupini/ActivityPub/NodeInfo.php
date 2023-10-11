<?php

namespace Plugin\Lipupini\ActivityPub;

use Plugin\Lipupini;
use ActivityPhp;
use Plugin\Lipupini\WebFinger\Exception;

// https://github.com/jhass/nodeinfo/blob/main/PROTOCOL.md

class NodeInfo extends Lipupini\Http\Request {
	public string $responseType = 'application/json; profile="https://raw.githubusercontent.com/instalution/lipupini/v2.x/plugin/Lipupini/ActivityPub/NodeSchema.json"';

	public function initialize() {
		if (!str_starts_with($_SERVER['REQUEST_URI'], $this->system->baseUriPath . '.well-known/nodeinfo')) {
			return;
		}

		if (!$this->clientAcceptsMimeTypes([
			'application/json',
			$this->system->debug ? 'text/html' : null,
		])) {
			throw new Exception('Invalid request type');
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

		header('Content-type: application/json');
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
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
				'atom',
			],
			'usage' => [
				'users' => [
					'total' => 420,
					'activeHalfYear' => 69,
					'activeMonth' => 0,
				],
				'localPosts' => 420,
				'localComments' => 69,
			],
			'openRegistrations' => 'false',
		];

		header('Content-type: application/json');
		echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}

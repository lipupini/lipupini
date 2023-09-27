<?php

namespace Plugin\Lipupini;

class ActivityPub {
	public static function getClientJsonAccept() {
		$pluginAcceptsMimes = [
			'application/json',
			'application/activity+json',
			'application/ld+json',
			'application/ld+json; profile="https://www.w3.org/ns/activitystreams',
		];

		// Can be comma-separated list so make it an array
		$clientAcceptsMimes = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT']));

		$matchedMime = false;

		foreach ($clientAcceptsMimes as $mime) {
			if (in_array($mime, $pluginAcceptsMimes, true)) {
				$matchedMime = true;
				break;
			}
		}

		return $matchedMime;
	}
}

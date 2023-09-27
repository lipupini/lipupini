<?php

namespace Plugin\Lipupini;

class ActivityPub {
	public static function getClientAccept($type) {
		switch ($type) {
			case 'html' :
				$relevantAcceptsMimes = [
					'text/html',
				];
				break;
			case 'json' :
				$relevantAcceptsMimes = [
					'application/json',
					'application/activity+json',
					'application/ld+json',
					'application/ld+json; profile="https://www.w3.org/ns/activitystreams',
				];
				break;
			default :
				throw new Exception('Unknown accept type');
		}

		// Can be comma-separated list so make it an array
		$clientAcceptsMimes = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT']));

		$matchedMime = false;

		foreach ($clientAcceptsMimes as $mime) {
			if (in_array($mime, $relevantAcceptsMimes, true)) {
				$matchedMime = true;
				break;
			}
		}

		return $matchedMime;
	}
}

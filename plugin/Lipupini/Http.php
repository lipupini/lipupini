<?php

namespace Plugin\Lipupini;

class Http {
	public static function getClientAccept($type) {
		// HTTP Accept header needs to be preset to proceed
		if (empty($_SERVER['HTTP_ACCEPT'])) {
			return false;
		}

		$relevantAcceptsMimes = match ($type) {
			'HTML' => [
				'text/html',
			],
			'ActivityPubJson' => [
				'application/activity+json',
				'application/ld+json',
				'application/ld+json; profile="https://www.w3.org/ns/activitystreams"',
			],
			'AtomXML' => [
				'application/atom+xml',
			],
			default => throw new Exception('Unknown accept type'),
		};

		// Can be comma-separated list so make it an array
		$clientAcceptsMimes = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT']));

		if (count($clientAcceptsMimes) > 20) {
			throw new Exception('Suspicious number of client accept MIMEs');
		}

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

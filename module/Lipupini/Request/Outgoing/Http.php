<?php

namespace Module\Lipupini\Request\Outgoing;

use Module\Lipupini\Request\Outgoing;

class Http {
	public function __construct(public string $cacheDir) { }

	private static function _request(string $url, string $method, array $headers, string $body = null) {
		$ch = curl_init($url);
		if ($method === 'POST') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			if ($body) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			}
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, static::_headersToCurlArray($headers));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$responseBody = curl_exec($ch);
		$responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		curl_close($ch);
		return [
			'code' => $responseCode,
			'body' => $responseBody ? json_decode($responseBody, false) : '',
		];
	}

	public static function get(string $url, array $headers = []) {
		return static::_request($url, 'GET', $headers);
	}

	public static function post(string $url, string $body, array $headers = []) {
		return static::_request($url, 'POST', $headers, $body);
	}

	private static function _headersToCurlArray($headers) {
		return array_map(function ($k, $v) {
			return "$k: $v";
		}, array_keys($headers), $headers);
	}

	public static function sendSigned(string $keyId, string $privateKeyPem, string $inboxUrl, string $body, array $headers) {
		$headers = Outgoing\Signature::sign(
			$keyId,
			$privateKeyPem,
			$inboxUrl,
			$body,
			$headers
		);

		$response = static::post($inboxUrl, $body, $headers);

		// Check for a 2xx HTTP Status Code response
		if (((string)$response['code'])[0] !== '2') {
			throw new Exception('Did not receive 2xx response to signed request', 400);
		}

		return $response;
	}
}

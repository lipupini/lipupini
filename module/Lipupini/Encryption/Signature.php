<?php

namespace Module\Lipupini\Encryption;

use phpseclib3\Crypt\PublicKeyLoader;

class Signature {
	/*
	* All the remaining methods are adapted from Pixelfed
	* source: https://github.com/pixelfed/pixelfed/blob/63a7879c29bfa2bbc4f8dd6bacac09bcdacb6f86/app/Util/ActivityPub/HttpSignature.php
	* thanks @dansup@pixelfed.social :) :)
	*/

	public static function signedRequest(string $privateKeyPath, string $keyId, string $url, $body = false, $extraHeaders = []): \Symfony\Component\HttpFoundation\Request {
		$headers = self::_headersToSign(
			$url,
			$body ? self::_digest($body) : false
		);
		$headers = array_merge($headers, $extraHeaders);
		$signedHeaders = implode(' ', array_map('strtolower', array_keys($headers)));
		$stringToSign = self::_headersToSigningString($headers);
		$rsa = PublicKeyLoader::loadPrivateKey(file_get_contents($privateKeyPath))->withHash('sha256');
		$signature = $rsa->sign($stringToSign);
		$signatureHeader = 'keyId="' . $keyId . '",algorithm="rsa-sha256",headers="' . $signedHeaders . '",signature="' . base64_encode($signature) . '"';
		unset($headers['(request-target)']);
		$headers['Signature'] = $signatureHeader;
		$request = \Symfony\Component\HttpFoundation\Request::create(
			$url,
			'POST',
			[], // parameters
			[], // cookies
			[], // files
			[], // $_SERVER,
			$body
		);
		foreach ($headers as $header => $value) {
			$request->headers->set($header, $value);
		}
		return $request;
	}

	private static function _digest($body) {
		return base64_encode(hash('sha256', $body, true));
	}

	protected static function _headersToSign(string $url, string|bool $digest = false, string $method = 'post'): array {
		$date = new \DateTime('UTC');

		if (!in_array($method, ['post', 'get'])) {
			throw new Exception('Invalid method used to sign headers in HttpSignature');
		}

		$parsed = parse_url($url);
		$url = $parsed['path'] . (!empty($parsed['query']) ? '?' . $parsed['query'] : '');

		$headers = [
			'(request-target)' => $method . ' ' . $url,
			'Host' => parse_url($url, PHP_URL_HOST),
			'Date' => $date->format('D, d M Y H:i:s \G\M\T'),
		];

		if ($digest) {
			$headers['Digest'] = 'SHA-256=' . $digest;
		}

		return $headers;
	}

	private static function _headersToSigningString($headers): string {
		return implode("\n", array_map(function($k, $v) {
			return $k === '(request-target)' ? $k . ' ' . $v : strtolower($k) . ': ' . $v;
		}, array_keys($headers), $headers));
	}
}

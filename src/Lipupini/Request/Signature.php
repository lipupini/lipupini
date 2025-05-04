<?php

namespace Module\Lipupini\Request;

use Module\Lipupini\Encryption;

class Signature {
	/*
	 * source: https://github.com/pixelfed/pixelfed/blob/dev/app/Util/ActivityPub/HttpSignature.php
	 * source: https://github.com/aaronpk/Nautilus/blob/master/app/ActivityPub/HTTPSignature.php
	 * thanks dansup & aaronpk!
	 */
	public static function sign(string $keyId, string $privateKeyPath, string $url, string $body = null, array $headers = []) {
		if ($body) {
			$digest = self::_digest($body);
		}
		$headersToSign = self::_headersToSign($url, $body ? $digest : false);
		$headersToSign = array_merge($headersToSign, $headers);
		$encryption = new Encryption\Key;
		$stringToSign = $encryption->httpHeadersToSigningString($headersToSign);
		$signedHeaders = implode(' ', array_map('strtolower', array_keys($headersToSign)));
		$signature = base64_encode($encryption->sign($privateKeyPath, $stringToSign));
		$signatureHeader = 'keyId="' . $keyId . '",headers="' . $signedHeaders . '",algorithm="rsa-sha256",signature="' . $signature . '"';
		unset($headersToSign['(request-target)']);
		$headersToSign['Signature'] = $signatureHeader;
		return $headersToSign;
	}

	private static function _digest($body) {
		if (is_array($body)) {
			$body = json_encode($body);
		}
		return base64_encode(hash('sha256', $body, true));
	}

	protected static function _headersToSign($url, $digest = false, $method = 'post') {
		$date = new \DateTime('UTC');
		if (!in_array($method, ['post', 'get'])) {
			throw new Exception('Invalid method used to sign headers in HttpSignature');
		}
		$headers = [
			'(request-target)' => $method . ' ' . parse_url($url, PHP_URL_PATH),
			'Host' => parse_url($url, PHP_URL_HOST),
			'Date' => $date->format('D, d M Y H:i:s \G\M\T'),
		];
		if ($digest) {
			$headers['Digest'] = 'SHA-256=' . $digest;
		}
		return $headers;
	}

	/*
	 * source: https://github.com/pixelfed/pixelfed/blob/dev/app/Util/ActivityPub/HttpSignature.php
	 * source: https://github.com/aaronpk/Nautilus/blob/master/app/ActivityPub/HTTPSignature.php
	 * thanks dansup & aaronpk!
	 */
	public static function parseSignatureHeader($signature) {
		$parts = explode(',', $signature);
		$signatureData = [];

		foreach ($parts as $part) {
			if (preg_match('/(.+)="(.+)"/', $part, $match)) {
				$signatureData[$match[1]] = $match[2];
			}
		}

		if (!isset($signatureData['keyId'])) {
			return [
				'error' => 'No keyId was found in the signature header. Found: ' . implode(', ', array_keys($signatureData))
			];
		}

		if (!filter_var($signatureData['keyId'], FILTER_VALIDATE_URL)) {
			return [
				'error' => 'keyId is not a URL: ' . $signatureData['keyId']
			];
		}

		if (!isset($signatureData['headers']) || !isset($signatureData['signature'])) {
			return [
				'error' => 'Signature is missing headers or signature parts'
			];
		}

		return $signatureData;
	}

	public static function verify($publicKeyPem, $inputHeaders, $path, $body) {
		$signatureData = static::parseSignatureHeader($inputHeaders['HTTP_SIGNATURE']);

		// Adapted from https://github.com/symfony/http-foundation/blob/6.3/ServerBag.php#L29
		foreach ($inputHeaders as $key => $value) {
			if (str_starts_with($key, 'HTTP_')) {
				$headerName = strtolower(substr($key, 5));
				unset($inputHeaders[$key]);
			} else if (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
				$headerName = strtolower($key);
			} else {
				continue;
			}
			$inputHeaders[str_replace('_', '-', $headerName)] = $value;
		}
		$digest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));
		$headersToSign = [];
		foreach (explode(' ', $signatureData['headers']) as $h) {
			if ($h == '(request-target)') {
				$headersToSign[$h] = 'post ' . $path;
			} elseif ($h == 'digest') {
				$headersToSign[$h] = $digest;
			} elseif (isset($inputHeaders[$h])) {
				$headersToSign[$h] = $inputHeaders[$h];
			}
		}
		$encryption = new Encryption\Key;
		$signingString = $encryption->httpHeadersToSigningString($headersToSign);
		return $encryption->verify($signingString, base64_decode($signatureData['signature']), $publicKeyPem);
	}
}

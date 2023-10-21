<?php

namespace Module\Lipupini\Request\Incoming;

use Module\Lipupini\Encryption;
use Module\Lipupini\Request;

class Signature {
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

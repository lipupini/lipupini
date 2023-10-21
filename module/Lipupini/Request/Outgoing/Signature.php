<?php

namespace Module\Lipupini\Request\Outgoing;

use Module\Lipupini\Encryption;
use Module\Lipupini\Request;

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
}

<?php

namespace Module\Lipupini\Encryption;

class Key {
	public const VALID_BITS = [512, 1024, 2048, 3072, 4096];

	public function generate(int $privateKeyBits, string $password = null): array {
		if (!in_array($privateKeyBits, $this::VALID_BITS, true)) {
			throw new Exception('Invalid bits');
		}

		// Configuration for the keypair
		$config = array(
			'private_key_bits' => $privateKeyBits,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		);

		// Generate the keypair
		$res = openssl_pkey_new($config);

		// Extract the private key
		openssl_pkey_export($res, $privateKey, $password);

		// Extract the public key
		$publicKey = openssl_pkey_get_details($res)['key'];

		return [
			'private' => $privateKey,
			'public' => $publicKey,
		];
	}

	public function generateAndSave(string $privateKeyPath, string $publicKeyPath, int $privateKeyBits, string $password = ''): void {
		$generated = $this->generate($privateKeyBits, $password);
		file_put_contents($publicKeyPath, $generated['public']);
		file_put_contents($privateKeyPath, $generated['private']);
	}

	public function sign(string $privateKeyPem, $message): string {
		openssl_sign($message, $signature, openssl_pkey_get_private($privateKeyPem), OPENSSL_ALGO_SHA256);
		return $signature;
	}

	public function verify($message, $signature, $publicKeyPem): bool {
		return openssl_verify($message, $signature, $publicKeyPem, OPENSSL_ALGO_SHA256);
	}

	public function httpHeadersToSigningString(array $headers): string {
		return implode("\n", array_map(function ($k, $v) {
			return strtolower($k) . ': ' . $v;
		}, array_keys($headers), $headers));
	}
}

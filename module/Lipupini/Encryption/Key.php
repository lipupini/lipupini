<?php

namespace Module\Lipupini\Encryption;

use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\Common\PublicKey;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class Key {
	public const VALID_BITS = [512, 1024, 2048, 3072, 4096];

	public function generate(int $privateKeyBits, string $password = ''): array {
		if (!in_array($privateKeyBits, $this::VALID_BITS, true)) {
			throw new Exception('Invalid bits');
		}

		if ($password) {
			$private = RSA::createKey($privateKeyBits)->withPassword($password);
		} else {
			$private = RSA::createKey($privateKeyBits);
		}

		$public = $private->getPublicKey();

		return [
			'private' => $private,
			'public' => $public,
		];
	}

	public function generateAndSave(string $privateKeyPath, string $publicKeyPath, int $privateKeyBits, string $password = ''): void {
		$generated = $this->generate($privateKeyBits, $password);
		file_put_contents($publicKeyPath, $generated['public']);
		file_put_contents($privateKeyPath, $generated['private']);
	}

	public function load(string $fullPath, string $type, string $password = ''): PublicKey | PrivateKey {
		switch ($type) {
			case 'public':
				return PublicKeyLoader::loadPublicKey(file_get_contents($fullPath));
			case 'private':
				if ($password) {
					return PublicKeyLoader::loadPrivateKey(file_get_contents($fullPath))->withPassword($password)->withHash('sha256');
				} else {
					return PublicKeyLoader::loadPrivateKey(file_get_contents($fullPath))->withHash('sha256');
				}
		}

		throw new Exception('Unknown key type');
	}

	public function sign(string $privateKeyPath, $message): string {
		return $this->load($privateKeyPath, 'private')->sign($message);
	}

	public function verify($message, $signature, $keyPath, $fromKeyType = 'public'): bool {
		return $this->load($keyPath, $fromKeyType)->verify($message, $signature);
	}

	public function encrypt($publicKeyPath, $data): string {
		return $this->load($publicKeyPath, 'public')->encrypt($data);
	}

	public function decrypt($privateKeyPath, $data): string {
		return $this->load($privateKeyPath, 'private')->decrypt($data);
	}
}

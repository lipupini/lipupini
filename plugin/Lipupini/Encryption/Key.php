<?php

namespace Plugin\Lipupini\Encryption;

use Spatie\Crypto\Rsa\KeyPair;
use Spatie\Crypto\Rsa\PrivateKey;
use Spatie\Crypto\Rsa\PublicKey;

class Key {
	public const VALID_BITS = [512, 1024, 2048, 3072, 4096];

	public function generate(int $privateKeyBits, string $password = ''): array {
		if (!in_array($privateKeyBits, $this::VALID_BITS, true)) {
			throw new Exception('Invalid bits');
		}

		[$private, $public] = (
			new KeyPair(OPENSSL_ALGO_SHA512, $privateKeyBits)
		)->password($password)->generate();

		return [
			'private' => $private,
			'public' => $public,
		];
	}

	public function generateAndSave(string $privateKeyPath, string $publicKeyPath, string $password = ''): void {
		$generated = $this->generate($password);
		file_put_contents($publicKeyPath, $generated['public']);
		file_put_contents($privateKeyPath, $generated['private']);
	}

	public function load(string $fullPath, string $type, string $password = ''): PrivateKey | PublicKey {
		return match ($type) {
			'public' => PublicKey::fromFile($fullPath),
			'private' => PrivateKey::fromFile($fullPath, $password),
		};
	}

	public function sign(string $privateKeyPath, $message): string {
		return $this->load($privateKeyPath, 'private')->sign($message);
	}

	public function verify($message, $keyPath, $fromKeyType, $signature): int | false{
		return $this->load($keyPath, $fromKeyType)->verify($message, $signature);
	}

	public function encrypt($privateKeyPath, $data): string {
		return $this->load($privateKeyPath, 'private')->encrypt($data);
	}

	public function decrypt($publicKeyPath, $data): string {
		return $this->load($publicKeyPath, 'public')->decrypt($data);
	}
}

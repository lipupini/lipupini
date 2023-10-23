<?php

namespace Module\Lipupini\ActivityPub;

use Module\Lipupini\WebFinger;
use Module\Lipupini\Request\Outgoing;

class RemoteActor {
	public array $cache = [];
	protected string $profileUrl;
	protected string $handle;
	protected string $host;

	public function __construct(protected string $cacheDir) { }

	public static function fromUrl(string $url, string $cacheDir) {
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new Exception('Invalid actor URL', 400);
		}

		$actor = new static($cacheDir);
		$actor->profileUrl = $url;
		$actor->host = parse_url($url, PHP_URL_HOST);
		return $actor;
	}

	public static function fromHandle(string $handle, string $cacheDir) {
		if ($handle === '@') {
			$handle = preg_replace('#^@#', '', $handle);
		}

		if (substr_count($handle, '@') !== 1) {
			throw new Exception('Suspicious remote actor handle format', 400);
		}

		if (!$handle) {
			throw new Exception('Could not determine remote actor handle', 400);
		}

		$actor = new static($cacheDir);

		$actor->handle = $handle;
		$actor->host = explode('@', $handle)[1];

		$webFinger = $actor->webFinger($handle);
		if (empty($webFinger->links)) {
			throw new Exception('Could not find WebFinger links', 400);
		}

		$apProfileUrl = null;
		foreach ($webFinger->links as $link) {
			if (
				!empty($link->rel) && $link->rel === 'self' &&
				!empty($link->type) && $link->type === 'application/activity+json'
			) {
				$apProfileUrl = $link->href;
				break;
			}
		}

		if (!$apProfileUrl) {
			throw new Exception('Could not find link for rel="self"', 400);
		}

		$actor->profileUrl = $apProfileUrl;
		return $actor;
	}

	public function webFinger(string $handle) {
		return $this->cached('webFinger', function() use ($handle) {
			return WebFinger\Remote::acct($handle);
		});
	}

	public function getId() {
		$profile = $this->getProfileJson();
		if (empty($profile->id)) {
			throw new Exception('Could not determine actor ID', 400);
		}
		return $profile->id;
	}

	public function getInboxUrl() {
		$profile = $this->getProfileJson();
		if (empty($profile->inbox) || !filter_var($profile->inbox, FILTER_VALIDATE_URL)) {
			throw new Exception('Could not determine inbox URL', 400);
		}
		return $profile->inbox;
	}

	public function getPreferredUsername() {
		$profile = $this->getProfileJson();
		if (empty($profile->preferredUsername)) {
			throw new Exception('Could not determine remote preferred username', 400);
		}
		return $profile->preferredUsername;
	}

	public function getPublicKeyPem() {
		$profile = $this->getProfileJson();
		if (empty($profile->publicKey->publicKeyPem)) {
			throw new Exception('Could not determine public key PEM', 400);
		}
		return $profile->publicKey->publicKeyPem;
	}

	public function getProfileJson() {
		return $this->cached('profile', function() {
			return Outgoing\Http::get($this->profileUrl, ['Accept' => Request::$mimeType])['body'];
		});
	}

	private function _getCacheDir() {
		return $this->cacheDir . '/' . $this->host . '/' . (!empty($this->profileUrl) ? md5($this->profileUrl) : explode('@', $this->handle)[0]);
	}

	public function cached(string $key, callable $value) {
		if (!empty($this->cache[$key])) {
			return $this->cache[$key];
		}

		$actorCacheDir = $this->_getCacheDir();
		$cacheFile = $actorCacheDir . '/' . $key . '.json';

		if (file_exists($cacheFile)) {
			return $this->cache[$key] = unserialize(file_get_contents($cacheFile));
		}

		if (!is_dir($actorCacheDir)) {
			mkdir($actorCacheDir, 0755, true);
		}

		$this->cache[$key] = $value();
		file_put_contents($cacheFile, serialize($this->cache[$key]));
		return $this->cache[$key];
	}
}

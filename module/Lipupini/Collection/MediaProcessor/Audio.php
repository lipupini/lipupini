<?php

namespace Module\Lipupini\Collection\MediaProcessor;

class Audio {
	use Trait\CacheSymlink;

	public static function mimeTypes(): array {
		return [
			'mp3' => 'audio/mp3',
			'm4a' => 'audio/m4a',
			'ogg' => 'audio/ogg',
			'flac' => 'audio/flac',
		];
	}
}

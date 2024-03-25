<?php

namespace Module\Lipupini\Collection\MediaProcessor;

class Video {
	use Trait\CacheSymlink;

	public static function mimeTypes(): array {
		return [
			'mp4' => 'video/mp4',
		];
	}
}

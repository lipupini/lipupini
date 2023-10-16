<?php

namespace Plugin\Lipupini\ActivityPub\Request;

use Plugin\Lipupini\ActivityPub\Request;

class Followers {
	public function __construct(Request $activityPubRequest) {
		if ($activityPubRequest->system->debug) {
			error_log('DEBUG: ' . get_called_class());
		}

		$activityPubRequest->system->responseContent = json_encode([], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}

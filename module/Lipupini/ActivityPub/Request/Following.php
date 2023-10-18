<?php

namespace Module\Lipupini\ActivityPub\Request;

use Module\Lipupini\ActivityPub\Request;

class Following {
	public function __construct(Request $activityPubRequest) {
		if ($activityPubRequest->system->debug) {
			error_log('DEBUG: ' . get_called_class());
		}

		$activityPubRequest->system->responseContent = json_encode([], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}

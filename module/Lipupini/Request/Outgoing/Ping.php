<?php

namespace Module\Lipupini\Request\Outgoing;

class Ping {
	public static function host(string $host) : bool {
		// Host without port
		exec('ping -c 1 ' . escapeshellarg(parse_url('//' . $host, PHP_URL_HOST)), $output, $resultCode);
		return $resultCode === 0;
	}
}

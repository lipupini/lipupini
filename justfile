default:
	just --list

# ---

# Start PHP's built-in webserver
serve port='4000':
	cd module/Lukinview/webroot && PHP_CLI_SERVER_WORKERS=2 php -S localhost:{{port}} index.php

# Proxy to `justfile` in `docker` directory
docker *args="":
	cd docker && just {{args}}

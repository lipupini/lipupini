default:
	just --list

# ---

# An idea for a way to grep a collection ActivityPub inbox
inbox account:
	ls -l --color=auto --format=single-column collection/{{account}}/.lipupini/inbox

# Start PHP's built-in webserver
serve port='4000':
	cd module/Lukinview/webroot && PHP_CLI_SERVER_WORKERS=2 php -S localhost:{{port}} index.php

# Build the Docker image, overwriting previous to save space
docker-build:
	docker build --rm --tag lipupini .

# Run a Docker container from the generated Docker image
docker-run:
	docker run --expose=4000 --network=host lipupini

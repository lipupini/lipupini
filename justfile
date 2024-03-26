# Display list of commands/recipes
default:
	just --list

# ---

# Open `system/config/state.php` in `vi`
config:
	printf '\n\n\nOpening `system/config/state.php` in `vi`...\n\n\n' && sleep 2.5 && vi system/config/state.php

# An idea for a way to grep a collection ActivityPub inbox
inbox account:
	ls -l --color=auto --format=single-column collection/{{account}}/.lipupini/inbox

# Start PHP's built-in webserver
serve port='4000':
	cd module/Lukinview/webroot && PHP_CLI_SERVER_WORKERS=2 php -S localhost:{{port}} index.php

# Build a Lipupini Docker image from `system/docker`
docker-build:
	docker build --rm --tag lipupini --file system/docker/Dockerfile .

# Run Docker container from a Lipupini Docker image
docker-run:
	cd system/docker && docker run --expose=4000 --network=host lipupini

default:
	just --list

# ---

# An idea for a way to grep an account inbox
inbox account:
	ls -l --color=auto --format=single-column collection/{{account}}/.lipupini/inbox

# Start PHP's built-in webserver
serve port='4000':
	cd module/Lukinview/webroot && PHP_CLI_SERVER_WORKERS=2 php -S localhost:{{port}} index.php

# Build the Docker image
docker-build:
	docker build --tag lipupini --file ./Dockerfile .

# Run a Docker container from the generated Docker image
docker-run:
	docker run lipupini

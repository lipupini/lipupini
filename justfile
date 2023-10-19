set dotenv-load := true
set positional-arguments

default:
	just --list

# ---

# Build the docker images, configuring the non-root user to match this machine
build:
	docker-compose -f docker/docker-compose.yml build --build-arg APP_UID=$(id -u) --build-arg APP_GID=$(id -g)

composer *args="":
	docker-compose run app composer "$@"

dc *args="":
	docker-compose -f docker/docker-compose.yml {{args}}

# Shut it down
down:
	docker-compose -f docker/docker-compose.yml down

logs:
	docker-compose -f docker/docker-compose.yml logs -f

# Access a running container instance
sh *args="":
	docker-compose -f docker/docker-compose.yml exec {{args}} app bash

# Build and run the containerized environment.
up *services: build
	docker-compose -f docker/docker-compose.yml up --no-build {{services}}

# Run without building first. This can be useful when developing offline.
up-only *services:
	docker-compose -f docker/docker-compose.yml up {{services}}


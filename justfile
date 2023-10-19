default:
	just --list

# ---

build:
	docker-compose -f docker/docker-compose.yml build --build-arg APP_UID=$(id -u) --build-arg APP_GID=$(id -g)

dc *args="":
	docker-compose -f docker/docker-compose.yml {{args}}

down:
	docker-compose -f docker/docker-compose.yml down

prod *args="":
	docker-compose -f docker/docker-compose.prod.yml {{args}}

sh *args="":
	docker-compose -f docker/docker-compose.yml exec {{args}} app bash

up *services: build
	docker-compose -f docker/docker-compose.yml up --no-build {{services}}


#!/usr/bin/env bash

docker build -t static-app -f static-build.Dockerfile .

docker cp $(docker create --name static-app-tmp static-app):/go/src/app/dist/frankenphp-linux-x86_64 lipupini ; docker rm static-app-tmp

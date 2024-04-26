#!/usr/bin/env bash

# Add Docker's official GPG key:
sudo apt-get update
sudo apt-get -y install ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

# Add the repository to Apt sources:
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update

DOCKER_VERSION_STRING=$(apt-cache madison docker-ce | awk '{ print $3 }' | grep '^5:26\.1' | sed 1q)
DOCKER_COMPOSE_PLUGIN_VERSION_STRING=$(apt-cache madison docker-compose-plugin | awk '{ print $3 }' | grep '^2\.26\.1' | sed 1q)

# Install Docker with `docker compose
sudo apt-get -y install docker-ce=$DOCKER_VERSION_STRING docker-compose-plugin=$DOCKER_COMPOSE_PLUGIN_VERSION_STRING

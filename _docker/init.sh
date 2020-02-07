#!/usr/bin/env bash

set -o nounset
set -o errexit
set -o pipefail

cp .env.docker .env
docker-compose up -d --build

# application specific commands
docker-compose exec php-fpm composer install

#!/usr/bin/env bash

### Output formatting
info() {
  time=$(date '+%H:%M:%S')
  echo -e "\033[94m$time | $1\033[39m"
}

success() {
  time=$(date '+%H:%M:%S')
  echo -e "\033[92m$time | $1\033[39m"
}

warning() {
  time=$(date '+%H:%M:%S')
  echo -e "\033[93m$time | $1\033[39m"
}

error() {
  time=$(date '+%H:%M:%S')
  echo -e "\033[91m$time | $1\033[39m"
}

### COMMANDS ###
install() {
    info "Starting setup"
    if [ ! -f .env ]; then
         cp .env.example .env
    fi

    directory=${PWD##*/}

    docker compose build
    docker compose up -d
    docker exec $directory-laravel.test-1 composer install
    docker exec $directory-laravel.test-1 php artisan migrate
    info "Pulling data for last 7 days"
    docker exec $directory-laravel.test-1 php artisan app:get-exchange-rates 7
    info "Building front-end"
    docker exec $directory-laravel.test-1 npm ci
    docker exec $directory-laravel.test-1 npm run build
    info "Build done app is available here: http://localhost:8080/"
}

up() {
    if ! docker info > /dev/null 2>&1; then
      error "Docker is not running on your machine, please run docker!"
      exit 1
    fi
    ./vendor/bin/sail up -d
}

down() {
    ./vendor/bin/sail down
}

restart() {
    down
    up
}

ssh() {
    up
    ./vendor/bin/sail shell
}

build() {
    docker compose build --no-cache
    docker compose up -d
}

if [[ -n "$1" ]]; then
command="$1"
if [[ -n "$(type -t "${command}")" ]] && [[ "$(type -t "${command}")" == function ]]; then
    "$command" "${@:2}"
else
    error "Command not found"
fi
else
    warning "Specify command"
fi

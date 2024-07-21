# Refactoring Test about Commissions

## Setup local env
- Install Docker Desktop https://docs.docker.com/get-docker/
- Install Git and setup access token to repository
- Clone repository in your folder
- Run in console `docker network create test`
- Run in console `docker-compose up -d`
- Open in terminal and run `bash`
- Run `composer install`

## Run commands
- Initial script `php app-old.php storage/input.txt`
- New one `php app.php storage/input.txt`
  - *before to run script on prod, change ENV to `production` in app.php

## Run Unit Tests
- php vendor/bin/codecept run unit


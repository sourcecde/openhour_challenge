# openhour_challenge

## Prerequisites

- PHP 7.1
- Mysql
- Composer

## Installation

- Clone git from the Repository (git clone https://github.com/sourcecde/openhour_challenge.git
)
- Allow webserver write to `runtime` and `web` folders

    chown -R www-data runtime web

- Install composer dependencies

    docker-compose run --rm php composer install

- Provision Database

    docker-compose run --rm php yii migrate

- open http://127.0.0.1:8000 in your browser

## API Documentation

- <a href="https://documenter.getpostman.com/view/1900475/T1LPESo1?version=latest">Restful API Documentation</a>

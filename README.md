# openhour_challenge

# openhour_challenge

## Prerequisites

- PHP 7.1
- Mysql
- Composer

## Installation

- Clone git from the Repository (git clone https://github.com/sourcecde/openhour_challenge.git
)
- composer update
- run the `/yii migrate` command to create the required tables
- Database username 'root' (username) in config/db.php file
- Change DB Password in config/db.php file (If any), otherwise leave blank(password)
```
    docker-compose run --rm php composer update --prefer-dist

    docker-compose run --rm php composer install
    
    docker-compose up -d

```

- RUN http://127.0.0.1:8000 in your browser

## API Documentation

- <a href="https://documenter.getpostman.com/view/1900475/T1LPESo1?version=latest">Restful API Documentation</a>

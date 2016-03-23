# Silex Twittos App

What is it? A minimal back-end twitter-like implementation developed with Silex.

What for? Intended to experiment developing SPA applications.

Done with: Silex 1.3 with [DoctrineOrmServiceProvider](https://github.com/dflydev/dflydev-doctrine-orm-service-provider) version 1.

## Instructions

Create a `config/settings.yml` file from the `config/settings.default.yml` one and enter the valid configuration parameters in your setup.

Then install dependencies with
```
composer install
```

Then launch server with
```
php -S localhost:8080 -t web/api web/index.php
```
Or
```
bin/server
```

Launch console with
```
php bin/console
```

# README #

FQS Finance

### What is this repository for? ###

* CRM
* Version 1.0

### How do I get set up? ###
* Install docker, docker compose (https://docs.docker.com/compose/install/)
* Create `dockers/mysql/dbdata` folder to keep databases updated for next running
* Start docker
`docker-compose up -d`
* Install dependencies for laravel
`docker-compose exec app composer install`
* Generate App Key
`docker-compose exec app php artisan key:generate`
* Install Laravel-laraadmin
`docker-compose exec app php artisan la:install`
* Open `http://localhost` on browser


* Crontab
  `0 0 1 * * php artisan customer-revenue:statistic` 
  `* * * * * php artisan order:cancel` 
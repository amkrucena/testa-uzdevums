## Test task

## Setup
Installed and Running docker required<br>
<h3>These are setup steps for Mac and Linux</h3>
* chmod +x ./startup.sh
* ./startup.sh install

<h3>For Windows</h3>
Replace {dir-name} with the name of project directory

* docker-compose build
* docker-compose up -d
* docker exec {dir-name}-laravel.test-1 composer install
* docker exec {dir-name}-laravel.test-1 php artisan migrate
* docker exec {dir-name}-laravel.test-1 php artisan app:get-exchange-rates 7
* docker exec {dir-name}-laravel.test-1 npm ci
* docker exec {dir-name}-laravel.test-1 npm run build

App will be available on  http://localhost:8080/

<h3>Required versions to run without docker</h3>

* php8.3
* node 22
* mysql 8
* composer 2
* redis

<h4> Install steps without docker</h4>

* Change .env to match your custom setup
* composer install
* php artisan migrate
* php artisan app:get-exchange-rates 7
* npm ci
* npm run build

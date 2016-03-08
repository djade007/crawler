# Mini Search Engine

This is a website crawler built with Laravel and Angularjs Material

## Installation

To install using composer

Run composer install

Make sure storage and bootstrap/cache are writable

Copy .env.example to .env

Run php artisan key:generate

Edit .env file to set up the database credentials

Run `php artisan crawl --site=nairaland` and `php artisan crawl --site=stackoverflow` to start crawling and indexing nairaland.com and stackoverflow.com respectively

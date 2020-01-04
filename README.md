# Laramodula

[![CircleCI](https://circleci.com/gh/kunsal/laramodula.svg?style=svg)](https://circleci.com/gh/kunsal/laramodula)
[![Laravel 5.x|6.x](https://img.shields.io/badge/Laravel-5.x|6.x-orange.svg)](http://laravel.com)
[![Latest Stable Version](https://img.shields.io/packagist/v/kunsal/laramodula.svg)](https://packagist.org/packages/kunsal/laramodula)
[![Latest Unstable Version](https://poser.pugx.org/kunsal/laramodula/v/unstable)](https://packagist.org/packages/kunsal/laramodula)
[![Total Downloads](https://poser.pugx.org/kunsal/laramodula/downloads)](https://packagist.org/packages/kunsal/laramodula)
[![GitHub issues](https://img.shields.io/github/issues/kunsal/laramodula)](https://github.com/kunsal/laramodula/issues)
[![GitHub forks](https://img.shields.io/github/forks/kunsal/laramodula)](https://github.com/kunsal/laramodula/network)
[![Latest Stable Version](https://img.shields.io/github/license/kunsal/laramodula.svg)](https://github.com/kunsal/laramodula/blob/master/LICENSE)


`Laramodula` is a Laravel package which is used to manage large Laravel app. As your app
 grows in complexity and size, managing it with the default Laravel folder structure 
 makes it less maintainable. Modularizing your app gives you more room for maintainability
 and extension. This is what this package does by grouping related controllers, models, 
 views etc into same folders called modules. 
 
 ## Structure
 
 

This provides a modular project structure scaffold in Laravel.

## Installation

### Using Composer

`composer require kunsal/laramodula`

### Manual Installation
Modify  your `composer.json` file to include:
```$xslt
{
    "require": {
        "kunsal/laramodula": "1.0.*"
    }
}
```
and run `composer install`

This package will be auto-discovered by Laravel at installation and so there is no need
add it to `config/app.php`.

## Usage
To generate a simple Blog module, run 

`php artisan make:module Blog`

You can optionally pass parameters to the generation script like so:

- Module with default migration scaffold

    `php artisan make:module Blog --migration`
    
- Module with default form: This module leverages on Kris\LaravelFormBuilder

    `php artisan make:module Blog --migration --form="title:text, body:textarea"`
    
  Check [https://kristijanhusak.github.io/laravel-form-builder/overview/commands.html](https://kristijanhusak.github.io/laravel-form-builder/overview/commands.html) for
  more on form field values and implementation of the form builder.

- Make a resource controller with boilerplate code with the flag `--resources`

Below is how a Blog module is structured when you run `php  artisan module:make`.
```
 app/
 |-- ... 
 |-- Modules/
 |---- Blogs/
 |------ Events/ 
 |------ Forms/ 
 |-------- BlogForm.php 
 |------ Http/
 |-------- Controllers/ 
 |---------- BlogController.php 
 |-------- Requests/
 |---------- StoreBlogRequest.php 
 |---------- UpdateBlogRequest.php 
 |-------- Services/
 |---------- CreateBlogService.php 
 |-------- routes.php 
 |------ Listeners/
 |------ Mail/
 |------ Models/
 |-------- Migrations/
 |---------- 2019_12_21_112619_create_blogs_table.php
 |-------- Blog.php 
 |------ Providers/
 |-------- BlogEventServiceProvider.php
 |-------- BlogServiceProvider.php
 |------ Repositories/
 |-------- Eloquent/
 |---------- BlogRepository.php
 |-------- BlogInterface.php
 |------ Resources/
 |-------- Lang/
 |-------- Views/
 |---------- index.blade.php
 |---------- form.blade.php
 |------ Traits/
 ```

## License
This package has an MIT License. Please see [Licence File](https://github.com/kunsal/laramodula/blob/master/LICENSE) for 
more.

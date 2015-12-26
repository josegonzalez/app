# A CakePHP Application Skeleton

[![Build Status](https://img.shields.io/travis/josegonzalez/app/master.svg?style=flat-square)](https://travis-ci.org/josegonzalez/app)
[![License](https://img.shields.io/packagist/l/josegonzalez/app.svg?style=flat-square)](https://packagist.org/packages/josegonzalez/app)

A fork of the [official skeleton](https://github.com/cakephp/app) for creating applications with [CakePHP](http://cakephp.org) 3.x.

The framework source code can be found here: [cakephp/cakephp](https://github.com/cakephp/cakephp).

## Installation

1. Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Run `php composer.phar create-project --prefer-dist josegonzalez/app [app_name]`.

If Composer is installed globally, run
```bash
composer create-project --prefer-dist josegonzalez/app [app_name]
```

You should now be able to visit the path to where you installed the app and see
the setup traffic lights.

## Configuration

Read and edit `config/app.php` and setup the 'Datasources' and any other
configuration relevant for your application.

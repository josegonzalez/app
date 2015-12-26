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

## Features

### Heroku Support

Heroku and other PaaS-software are supported by default. If deploying to Heroku, simply run the following and - assuming you have the proper remote configuration - everything should work as normal:

```shell
git push heroku master
```

Migrations for the core application will run by default. If you wish to run migrations for plugins, you will need to modify the key `scripts.compile` in your `composer.json`.

### Installed Plugins

The following is a list of plugins installed and pre-configured:

- friendsofcake/crud
- friendsofcake/crud-view
- friendsofcake/bootstrap-ui
- friendsofcake/search
- josegonzalez/cakephp-upload

### Configuration

By default, this skeleton will load configuration from the following files:

- `config/app.php`
- `config/.env`
    - if this file does not exist, `config/.env.default`

For "global" configuration that does not change between environments, you should modify `config/app.php`. As this file is ignored by default, you should *also* endeavor to add sane defaults to `app.default.php`.

For configuration that varies between environments, you should modify the `config/.env` file. This file is a bash-compatible file that contains `export KEY_1=VALUE` statements. Underscores in keys are used to expand the key into a nested array, similar to how `\Cake\Utility\Hash::expand()` works.

As a convenience, certain variables are remapped automatically by the `config/env.php` file. You may add other paths at your leisure to this file.

### Error Handling

Custom error handlers that ship errors to external error tracking services are set via `josegonzalez/php-error-handers`. To configure one, you can add the following key configuration to your `config/app.php`:

```php
[
    'Error' => [
        'config' => [
            'handlers' => [
                // configuring the BugsnagHandler via an env var
                'BugsnagHandler' => [
                    'apiKey' => env('BUGSNAG_APIKEY', null)
                ],
            ],
        ],
    ],
];
```

Then simply set the proper environment variable in your `config/.env` or in your platform's configuration management tool.

### Queuing

You can start a queue off the `jobs` mysql table:

```shell
# ensure everything is migrated and the jobs table exists
bin/cake migrations migrate

# default queue
bin/cake queuesadilla

# also the default queue
bin/cake queuesadilla --queue default

# some other queue
bin/cake queuesadilla --queue some-other-default

# use a different engine
bin/cake queuesadilla --engine redis
```

You can customize the engine configuration under the `Queuesadilla.engine` array in `config/app.php`. At the moment, it defaults to a config compatible with your application's mysql database config.

Need to queue something up?

```php
// assuming mysql engine
use josegonzalez\Queuesadilla\Engine\MysqlEngine;
use josegonzalez\Queuesadilla\Queue;

// get the engine config:
$config = Configure::read('Queuesadilla.engine');

// instantiate the things
$engine = new MysqlEngine($config);
$queue = new Queue($engine);

// a function in the global scope
function some_job($job)
{
    var_dump($job->data());
}
$queue->push('some_job', [
    'id' => 7,
    'message' => 'hi'
]);
```

See [here](https://github.com/josegonzalez/php-queuesadilla/blob/master/docs/defining-jobs.md) for more information on defining jobs.

> One nice thing that *could* be implemented is a registry pattern around engines. You could maybe configure multiple engines - similar to how one might do so for caches or logging - and then pull them out using an `ObjectRegistry`. Future enhancement I guess.

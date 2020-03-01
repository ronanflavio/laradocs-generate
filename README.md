### Installation

PHP 7.2 and Laravel 6.x or higher are required.

```shell script
composer require --dev ronanflavio/laradocs-generate
```

### Publishing

Publish the config file by running:

```shell script
php artisan vendor:publish --provider="Ronanflavio\LaradocsGenerate\LaradocsGenerateServiceProvider" --tag=laradocs-config
```

This will create the `docs.php` file in your `config` directory.

You can also publish the view blade file by running:

```shell script
php artisan vendor:publish --provider="Ronanflavio\LaradocsGenerate\LaradocsGenerateServiceProvider" --tag=laradocs-views
```

This will create the `docs.blade.php` file in your `resource/views` directory.

### License

The Laradocs Generate is free software licensed under the MIT license.

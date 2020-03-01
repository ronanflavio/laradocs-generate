### Installation

PHP 7.2 and Laravel 6.x or higher are required.

```shell script
composer require --dev ronanflavio/laradocs-generate
```

After updating composer, add the service provider to the `providers` array in `config/app.php`

```
Ronanflavio\LaradocsGenerate\LaradocsGenerateServiceProvider::class,
```

### Documenting

Tha main goal of this package is to indicate which specifically is the URI parameters, the request body parameters and what is coming within the response.

To achieve this, you may want to write some custom PHPDocs above your controller class, actions and your DTOs' properties. Let's see some practical examples:

#### Typing the request and response objects

To indicate which is the URI parameter type, use the default `@param` annotation.

To indicate which class object must be given within the request, you must write the full qualified name of it's class within the `@request` annotation.

To indicate which class object will be returned, you must write the full qualified name of it's class within the `@response` annotation. If there is no class object to be returned, just type the the variable type (e.g.: `boolean`, `int` etc...) instead. `void` will be provided if there is no `@response`.

```php
<?php

// namespace and uses here...

/**
 * Users management
 * 
 * Here will be shown the description of
 * your UsersController group. If there is
 * no description, the class name will be
 * shown instead.
 */
class UsersController extends Controller
{
    /**
     * Lists all users
     * @response \App\DataTransferObjects\User\ListUsersDto
     */
    public function index()
    {
        // your code here...
    }

    /**
     * Returns the details about the given user
     * @param string $id
     * @response \App\DataTransferObjects\User\UserDetailsDto
     */
    public function details(string $id)
    {
        // your code here...
    }
    
    /**
     * Creates a new user
     * @request \App\DataTransferObjects\User\CreateUserDto
     * @response \App\DataTransferObjects\User\UserDetailsDto
     */
    public function create(CreateUserRequest $request)
    {
        // your code here...
    }
```

#### Typing the DTOs' attributes specifications

To indicate which is the variable type from an attribute you may use the standard `@var` annotation. If you want to be more specific, you can also indicate a practical example to that attribute using the `@example` annotation. See some code bellow:

```php
<?php

namespace App\DataTransferObjects\User;

use App\DataTransferObjects\DataTransferObject;

class CreateUserDto extends DataTransferObject
{
    /**
     * @var string
     * @example email@example.local
     */
    public $email;

    /**
     * @var string
     * @example John Doe
     */
    public $name;

    /**
     * @var string
     * @example 123456
     */
    public $password;

    /**
     * @var string
     * @example 4c0c9e5d-f18f-4197-8531-02c90f9a81e0
     */
    public $role_id;
}

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

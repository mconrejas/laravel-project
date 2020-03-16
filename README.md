## Server Requirements

- PHP >= 7.1.3
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension

## Installation

- Clone repo

- Create .env file and copy the contents of the .env.example file. 
```
cp .env.example .env
```

- Put the correct DB credentials on the .env file.

- Run `composer install` to pull dependencies.

- Run `npm install && npm run dev` to compile assets.

- Setup Socket.IO server by running `npm install -g laravel-echo-server`. More info can be found [here](https://github.com/tlaverdure/laravel-echo-server)

- Create a virtualhost or nginx config and point it to the public directory.

That's it.

## Contribution Guide

As much as possible apply [SOLID Principles](https://laracasts.com/series/solid-principles-in-php). 


### Coding Style
Please follow [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding standard and the [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) autoloading standard.

### Rules
- Your editor tab should be equal to 4 spaces

- Name your branch in this convention `dev-<name>_BI-<ticket number>`

- Variable names should have sense. Not like
```php
$x = User::find(1));
```
Here's an example of a good variable naming
```php
$user  = User::find(1);
```

- Document every method you create. Below is an example

```php
    /**
     * Register a binding with the container.
     *
     * @param  string|array  $abstract
     * @param  \Closure|string|null  $concrete
     * @param  bool  $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        //
    }
```
- Always validate user requests|forms.

- Test, test and test.

- Create a PR when your task is done.

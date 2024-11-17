# SdFramework - PSR-Compatible PHP MVC Framework

A lightweight, modern, and PSR-compatible PHP MVC framework designed for building web applications.

## Features

- PSR-4 Autoloading
- PSR-11 Container Implementation
- Modern PHP 8.1+ Support
- Dependency Injection Container
- Routing System
- Request/Response Handling
- Configuration Management
- Clean Architecture

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

```bash
composer install
```

## Basic Usage

1. Create a new route in `public/index.php`:

```php
$app->getRouter()->get('/hello', function(Request $request) {
    return new Response('Hello World!');
});
```

2. Create a controller:

```php
namespace App\Controllers;

use SdFramework\Http\Request;
use SdFramework\Http\Response;

class HomeController
{
    public function index(Request $request): Response
    {
        return new Response('Welcome to SdFramework!');
    }
}
```

3. Register the controller route:

```php
$app->getRouter()->get('/', [HomeController::class, 'index']);
```

## Directory Structure

```
/project-root
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Views/
├── config/
├── public/
│   └── index.php
├── src/
│   ├── Container/
│   ├── Http/
│   └── Routing/
├── tests/
├── vendor/
├── composer.json
└── README.md
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License.

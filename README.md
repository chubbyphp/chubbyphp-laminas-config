# chubbyphp-laminas-config

[![Build Status](https://api.travis-ci.org/chubbyphp/chubbyphp-laminas-config.png?branch=master)](https://travis-ci.org/chubbyphp/chubbyphp-laminas-config)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-laminas-config/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-laminas-config?branch=master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-laminas-config/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-laminas-config/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-laminas-config/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config)
[![Daily Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-laminas-config/d/daily)](https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config)

## Description

Chubbyphp container adapter using laminas service manager configuration.

## Requirements

 * php: ^7.2
 * [chubbyphp/chubbyphp-container][2]: ^1.2

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-laminas-config][1].

```sh
composer require chubbyphp/chubbyphp-laminas-config "^1.0"
```

## Usage

```php
<?php

declare(strict_types=1);

use Chubbyphp\Laminas\Config\Config;
use Chubbyphp\Laminas\Config\ContainerFactory;

$factory = new ContainerFactory();

$container = $factory(
    new Config([
        'dependencies' => [
            'services'   => [],
            'invokables' => [],
            'factories'  => [],
            'aliases'    => [],
            'delegators' => [],
        ],
        // ... other configuration
    ])
);
```

The `dependencies` sub associative array can contain the following keys:

- `services`: an associative array that maps a key to a specific service instance.
- `invokables`: an associative array that map a key to a constructor-less
  service; i.e., for services that do not require arguments to the constructor.
  The key and service name usually are the same; if they are not, the key is
  treated as an alias.
- `factories`: an associative array that maps a service name to a factory class
  name, or any callable. Factory classes must be instantiable without arguments,
  and callable once instantiated (i.e., implement the `__invoke()` method).
- `aliases`: an associative array that maps an alias to a service name (or
  another alias).
- `delegators`: an associative array that maps service names to lists of
  delegator factory keys, see the
  [Mezzio delegators documentation](https://docs.laminas.dev/laminas-servicemanager/delegators/)
  for more details.

> Please note, that the whole configuration is available in the `$container`
> on `config` key:
>
> ```php
> $config = $container->get('config');
> ```

### Using with Mezzio

Replace the contents of `config/container.php` with the following:

```php
<?php

declare(strict_types=1);

use Chubbyphp\Laminas\Config\Config;
use Chubbyphp\Laminas\Config\ContainerFactory;

$config  = require __DIR__ . '/config.php';
$factory = new ContainerFactory();

return $factory(new Config($config));
```

## Copyright

Dominik Zogg 2020

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config
[2]: https://packagist.org/packages/chubbyphp/chubbyphp-container

<?php

declare(strict_types=1);

namespace Chubbyphp\Laminas\Config;

use Chubbyphp\Container\Container;

interface ConfigInterface
{
    public function configureContainer(Container $container): void;
}

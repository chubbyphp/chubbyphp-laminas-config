<?php

declare(strict_types=1);

namespace Chubbyphp\Laminas\Config;

use Chubbyphp\Container\ContainerInterface;

interface ConfigInterface
{
    public function configureContainer(ContainerInterface $container): void;
}

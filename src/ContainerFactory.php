<?php

declare(strict_types=1);

namespace Chubbyphp\Laminas\Config;

use Chubbyphp\Container\ContainerInterface;
use Chubbyphp\Container\MinimalContainer;

final class ContainerFactory
{
    public function __invoke(ConfigInterface $config, ?ContainerInterface $container = null): ContainerInterface
    {
        $container = $container ?? new MinimalContainer();
        $config->configureContainer($container);

        return $container;
    }
}

<?php

declare(strict_types=1);

namespace Chubbyphp\Laminas\Config;

use Chubbyphp\Container\Container;

final class ContainerFactory
{
    public function __invoke(ConfigInterface $config, ?Container $container = null): Container
    {
        $container = $container ?? new Container();
        $config->configureContainer($container);

        return $container;
    }
}

<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\TestAsset;

use Psr\Container\ContainerInterface;

final class Factory2
{
    public function __invoke(ContainerInterface $container, string $name)
    {
        return new Invokable2();
    }
}

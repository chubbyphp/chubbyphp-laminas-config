<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\TestAsset;

use Psr\Container\ContainerInterface;

final class Delegator1
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $object = $callback();
        $object->key1 = 'value1';

        return $object;
    }
}

<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\TestAsset;

use Psr\Container\ContainerInterface;

final class Delegator2
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $object = $callback();
        $object->key2 = 'value2';

        return $object;
    }
}

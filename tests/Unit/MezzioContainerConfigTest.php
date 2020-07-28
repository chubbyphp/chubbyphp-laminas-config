<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\Unit;

use Chubbyphp\Laminas\Config\Config;
use Chubbyphp\Laminas\Config\ContainerFactory;
use Laminas\ContainerConfigTest\AbstractMezzioContainerConfigTest;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
final class MezzioContainerConfigTest extends AbstractMezzioContainerConfigTest
{
    protected function createContainer(array $config): ContainerInterface
    {
        $factory = new ContainerFactory();

        return $factory(new Config(['dependencies' => $config]));
    }
}

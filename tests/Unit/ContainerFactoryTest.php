<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\Unit;

use Chubbyphp\Container\Container;
use Chubbyphp\Laminas\Config\ConfigInterface;
use Chubbyphp\Laminas\Config\ContainerFactory;
use Chubbyphp\Mock\Argument\ArgumentInstanceOf;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Chubbyphp\Laminas\Config\ContainerFactory
 */
final class ContainerFactoryTest extends TestCase
{
    use MockByCallsTrait;

    public function testFactoryWithoutGivenContainer(): void
    {
        $config = $this->getMockByCalls(ConfigInterface::class, [
            Call::create('configureContainer')->with(new ArgumentInstanceOf(Container::class)),
        ]);

        $factory = new ContainerFactory();
        $factory($config);
    }

    public function testFactoryWithGivenContainer(): void
    {
        $container = new Container();

        $config = $this->getMockByCalls(ConfigInterface::class, [
            Call::create('configureContainer')->with($container),
        ]);

        $factory = new ContainerFactory();
        $factory($config, $container);
    }
}

<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\Unit;

use Chubbyphp\Container\ContainerInterface;
use Chubbyphp\Container\MinimalContainer;
use Chubbyphp\Laminas\Config\ConfigInterface;
use Chubbyphp\Laminas\Config\ContainerFactory;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockObjectBuilder;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Chubbyphp\Laminas\Config\ContainerFactory
 */
final class ContainerFactoryTest extends TestCase
{
    public function testFactoryWithoutGivenContainer(): void
    {
        $builder = new MockObjectBuilder();

        $config = $builder->create(ConfigInterface::class, [
            new WithCallback('configureContainer', static function ($container): void {
                self::assertInstanceOf(ContainerInterface::class, $container);
            }),
        ]);

        $factory = new ContainerFactory();
        $factory($config);
    }

    #[DoesNotPerformAssertions]
    public function testFactoryWithGivenContainer(): void
    {
        $container = new MinimalContainer();
        $builder = new MockObjectBuilder();

        $config = $builder->create(ConfigInterface::class, [
            new WithoutReturn('configureContainer', [$container]),
        ]);

        $factory = new ContainerFactory();
        $factory($config, $container);
    }
}

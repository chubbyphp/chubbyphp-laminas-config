<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\Unit;

use Chubbyphp\Container\Exceptions\ContainerException;
use Chubbyphp\Container\MinimalContainer;
use Chubbyphp\Laminas\Config\Config;
use Chubbyphp\Mock\MockByCallsTrait;
use Chubbyphp\Tests\Laminas\Config\TestAsset\Delegator1;
use Chubbyphp\Tests\Laminas\Config\TestAsset\Delegator2;
use Chubbyphp\Tests\Laminas\Config\TestAsset\Factory1;
use Chubbyphp\Tests\Laminas\Config\TestAsset\Factory2;
use Chubbyphp\Tests\Laminas\Config\TestAsset\Invokable1;
use Chubbyphp\Tests\Laminas\Config\TestAsset\Invokable2;
use Chubbyphp\Tests\Laminas\Config\TestAsset\Invokable3;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Chubbyphp\Laminas\Config\Config
 */
final class ConfigTest extends TestCase
{
    use MockByCallsTrait;

    public function testNone(): void
    {
        $container = new MinimalContainer();

        $config = new Config(['key' => 'value']);
        $config->configureContainer($container);

        self::assertTrue($container->has('config'));

        self::assertSame(['key' => 'value'], $container->get('config'));
    }

    public function testServices(): void
    {
        $container = new MinimalContainer();

        $service1 = new Invokable1();
        $service2 = new Invokable2();

        $config = new Config([
            'dependencies' => [
                'services' => [
                    'name1' => $service1,
                    'name2' => $service2,
                ],
            ],
        ]);

        $config->configureContainer($container);

        self::assertTrue($container->has('name1'));
        self::assertTrue($container->has('name2'));

        self::assertSame($service1, $container->get('name1'));
        self::assertSame($service2, $container->get('name2'));
    }

    public function testInvokables(): void
    {
        $container = new MinimalContainer();

        $config = new Config([
            'dependencies' => [
                'invokables' => [
                    Invokable1::class,
                    Invokable2::class => Invokable2::class,
                    'name' => Invokable3::class,
                ],
            ],
        ]);

        $config->configureContainer($container);

        self::assertTrue($container->has(Invokable1::class));
        self::assertTrue($container->has(Invokable2::class));
        self::assertTrue($container->has(Invokable3::class));
        self::assertTrue($container->has('name'));

        self::assertInstanceOf(Invokable1::class, $container->get(Invokable1::class));
        self::assertInstanceOf(Invokable2::class, $container->get(Invokable2::class));
        self::assertInstanceOf(Invokable3::class, $container->get(Invokable3::class));
        self::assertInstanceOf(Invokable3::class, $container->get('name'));

        self::assertSame($container->get(Invokable3::class), $container->get('name'));
    }

    public function testInvokablesWithoutClass(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Could not create service with id "name"');

        $container = new MinimalContainer();

        $config = new Config([
            'dependencies' => [
                'invokables' => [
                    'name' => 'invalidFactory',
                ],
            ],
        ]);

        $config->configureContainer($container);

        $container->get('name');
    }

    public function testFactories(): void
    {
        $container = new MinimalContainer();

        $config = new Config([
            'dependencies' => [
                'factories' => [
                    Invokable1::class => Factory1::class,
                    Invokable2::class => new Factory2(),
                ],
            ],
        ]);

        $config->configureContainer($container);

        self::assertTrue($container->has(Invokable1::class));
        self::assertTrue($container->has(Invokable2::class));

        self::assertInstanceOf(Invokable1::class, $container->get(Invokable1::class));
        self::assertInstanceOf(Invokable2::class, $container->get(Invokable2::class));
    }

    public function testFactoriesWithoutFactory(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Could not create service with id "name"');

        $container = new MinimalContainer();

        $config = new Config([
            'dependencies' => [
                'factories' => [
                    'name' => 'invalidFactory',
                ],
            ],
        ]);

        $config->configureContainer($container);

        $container->get('name');
    }

    public function testAliases(): void
    {
        $container = new MinimalContainer();

        $config = new Config([
            'dependencies' => [
                'factories' => [
                    Invokable1::class => Factory1::class,
                ],
                'aliases' => [
                    'name1' => Invokable1::class,
                    'name2' => Invokable1::class,
                ],
            ],
        ]);

        $config->configureContainer($container);

        self::assertTrue($container->has(Invokable1::class));
        self::assertTrue($container->has('name1'));
        self::assertTrue($container->has('name2'));

        self::assertInstanceOf(Invokable1::class, $container->get(Invokable1::class));
        self::assertInstanceOf(Invokable1::class, $container->get('name1'));
        self::assertInstanceOf(Invokable1::class, $container->get('name2'));

        self::assertSame($container->get(Invokable1::class), $container->get('name1'));
        self::assertSame($container->get(Invokable1::class), $container->get('name2'));
    }

    public function testDelegators(): void
    {
        $container = new MinimalContainer();

        $config = new Config([
            'dependencies' => [
                'services' => [
                    'name2' => new \stdClass(),
                ],
                'invokables' => [
                    'name1' => \stdClass::class,
                ],
                'delegators' => [
                    'name2' => [
                        Delegator1::class,
                        new Delegator2(),
                    ],
                    \stdClass::class => [
                        Delegator1::class,
                        new Delegator2(),
                    ],
                    'name1' => [],
                ],
            ],
        ]);

        $config->configureContainer($container);

        self::assertTrue($container->has(\stdClass::class));
        self::assertTrue($container->has('name1'));

        self::assertSame($container->get(\stdClass::class), $container->get('name1'));

        $service1 = $container->get(\stdClass::class);

        self::assertInstanceOf(\stdClass::class, $service1);

        self::assertSame('value1', $service1->key1);
        self::assertSame('value2', $service1->key2);

        $service2 = $container->get('name2');

        self::assertInstanceOf(\stdClass::class, $service2);

        self::assertSame([], (array) $service2);
    }
}

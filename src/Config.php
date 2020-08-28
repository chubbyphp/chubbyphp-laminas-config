<?php

declare(strict_types=1);

namespace Chubbyphp\Laminas\Config;

use Chubbyphp\Container\Container;
use Chubbyphp\Container\Parameter;
use Psr\Container\ContainerInterface;

final class Config implements ConfigInterface
{
    /**
     * @var array<mixed>
     */
    private $config;

    /**
     * @param array<mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function configureContainer(Container $container): void
    {
        $container->factory('config', new Parameter($this->config));

        if (!isset($this->config['dependencies'])) {
            return;
        }

        $dependencies = $this->config['dependencies'];

        $dependencies['services'] = $dependencies['services'] ?? [];
        $dependencies['invokables'] = $dependencies['invokables'] ?? [];
        $dependencies['factories'] = $dependencies['factories'] ?? [];
        $dependencies['aliases'] = $this->aliases($dependencies['aliases'] ?? [], $dependencies['invokables']);
        $dependencies['delegators'] = $dependencies['delegators'] ?? [];

        $this->addServices($container, $dependencies['services']);
        $this->addInvokables($container, $dependencies['invokables']);
        $this->addFactories($container, $dependencies['factories']);
        $this->addAliases($container, $dependencies['aliases']);
        $this->addDelegators(
            $container,
            $dependencies['delegators'],
            $dependencies['services'],
            $dependencies['aliases']
        );
    }

    /**
     * @param array<string, string>     $aliases
     * @param array<string|int, string> $invokables
     *
     * @return array<string, string>
     */
    private function aliases(array $aliases, array $invokables): array
    {
        foreach ($invokables as $name => $invokable) {
            if (!is_int($name) && $name !== $invokable) {
                $aliases[$name] = $invokable;
            }
        }

        return $aliases;
    }

    /**
     * @param array<string, object> $services
     */
    private function addServices(Container $container, array $services): void
    {
        foreach ($services as $name => $service) {
            $container->factory($name, static function () use ($service) {
                return $service;
            });
        }
    }

    /**
     * @param array<string, string> $invokables
     */
    private function addInvokables(Container $container, array $invokables): void
    {
        foreach ($invokables as $invokable) {
            $container->factory($invokable, static function () use ($invokable) {
                return new $invokable();
            });
        }
    }

    /**
     * @param array<string, string|callable> $factories
     */
    private function addFactories(Container $container, array $factories): void
    {
        foreach ($factories as $name => $factory) {
            $container->factory($name, static function (ContainerInterface $container) use ($name, $factory) {
                if (is_string($factory) && class_exists($factory)) {
                    $factory = new $factory();
                }

                return $factory($container, $name);
            });
        }
    }

    /**
     * @param array<string, string> $aliases
     */
    private function addAliases(Container $container, array $aliases): void
    {
        foreach ($aliases as $alias => $target) {
            $container->factory($alias, static function (ContainerInterface $container) use ($target) {
                return $container->get($target);
            });
        }
    }

    /**
     * @param array<string, array<int, string|callable>> $delegators
     * @param array<string, object>                      $services
     * @param array<string, string>                      $aliases
     */
    private function addDelegators(
        Container $container,
        array $delegators,
        array $services,
        array $aliases
    ): void {
        foreach ($delegators as $name => $delegatorList) {
            if (isset($services[$name]) || isset($aliases[$name])) {
                continue;
            }

            foreach ($delegatorList as $delegator) {
                $container->factory(
                    $name,
                    static function (ContainerInterface $container, callable $previous) use ($name, $delegator) {
                        if (is_string($delegator) && class_exists($delegator)) {
                            $delegator = new $delegator();
                        }

                        return $delegator($container, $name, static function () use ($container, $previous) {
                            return $previous($container);
                        });
                    }
                );
            }
        }
    }
}

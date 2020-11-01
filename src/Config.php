<?php

declare(strict_types=1);

namespace Chubbyphp\Laminas\Config;

use Chubbyphp\Container\ContainerInterface;
use Chubbyphp\Container\Parameter;

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

    public function configureContainer(ContainerInterface $container): void
    {
        $container->factory('config', new Parameter($this->config));

        if (!isset($this->config['dependencies'])) {
            return;
        }

        $dependencies = $this->config['dependencies'];

        $services = $dependencies['services'] ?? [];
        $invokables = $dependencies['invokables'] ?? [];
        $factories = $dependencies['factories'] ?? [];
        $aliases = $this->aliases($dependencies['aliases'] ?? [], $invokables);
        $delegators = $dependencies['delegators'] ?? [];

        $this->addServices($container, $services);
        $this->addInvokables($container, $invokables);
        $this->addFactories($container, $factories);
        $this->addAliases($container, $aliases);
        $this->addDelegators($container, $delegators, $services, $aliases);
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
    private function addServices(ContainerInterface $container, array $services): void
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
    private function addInvokables(ContainerInterface $container, array $invokables): void
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
    private function addFactories(ContainerInterface $container, array $factories): void
    {
        foreach ($factories as $name => $factory) {
            $container->factory($name, static function (ContainerInterface $psrContainer) use ($name, $factory) {
                if (is_string($factory) && class_exists($factory)) {
                    $factory = new $factory();
                }

                return $factory($psrContainer, $name);
            });
        }
    }

    /**
     * @param array<string, string> $aliases
     */
    private function addAliases(ContainerInterface $container, array $aliases): void
    {
        foreach ($aliases as $alias => $target) {
            $container->factory($alias, static function (ContainerInterface $psrContainer) use ($target) {
                return $psrContainer->get($target);
            });
        }
    }

    /**
     * @param array<string, array<int, string|callable>> $delegators
     * @param array<string, object>                      $services
     * @param array<string, string>                      $aliases
     */
    private function addDelegators(
        ContainerInterface $container,
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
                    static function (ContainerInterface $psrContainer, callable $previous) use ($name, $delegator) {
                        if (is_string($delegator) && class_exists($delegator)) {
                            $delegator = new $delegator();
                        }

                        return $delegator($psrContainer, $name, static function () use ($psrContainer, $previous) {
                            return $previous($psrContainer);
                        });
                    }
                );
            }
        }
    }
}

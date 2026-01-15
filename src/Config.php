<?php

declare(strict_types=1);

namespace Chubbyphp\Laminas\Config;

use Chubbyphp\Container\ContainerInterface;
use Chubbyphp\Container\Parameter;

final class Config implements ConfigInterface
{
    /**
     * @param array<mixed> $config
     */
    public function __construct(private readonly array $config) {}

    public function configureContainer(ContainerInterface $container): void
    {
        $container->factory('config', new Parameter($this->config));

        if (!isset($this->config['dependencies'])) {
            return;
        }

        /** @var array{services?: array<string, object>, invokables?: array<string, string>, factories?: array<string, string>, aliases?: array<string, string>, delegators?: array<string, array<int, string>>} */
        $dependencies = $this->config['dependencies'];

        /** @var array<string, object> */
        $services = $dependencies['services'] ?? [];

        /** @var array<string, string> */
        $invokables = $dependencies['invokables'] ?? [];

        /** @var array<string, string> */
        $factories = $dependencies['factories'] ?? [];
        $aliases = $this->aliases($dependencies['aliases'] ?? [], $invokables);

        /** @var array<string, array<int, string>> */
        $delegators = $dependencies['delegators'] ?? [];

        $this->addServices($container, $services);
        $this->addInvokables($container, $invokables);
        $this->addFactories($container, $factories);
        $this->addAliases($container, $aliases);
        $this->addDelegators($container, $delegators, $services, $aliases);
    }

    /**
     * @param array<string, string>     $aliases
     * @param array<int|string, string> $invokables
     *
     * @return array<string, string>
     */
    private function aliases(array $aliases, array $invokables): array
    {
        foreach ($invokables as $name => $invokable) {
            if (!\is_int($name) && $name !== $invokable) {
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
            $container->factory($name, static fn () => $service);
        }
    }

    /**
     * @param array<string, string> $invokables
     */
    private function addInvokables(ContainerInterface $container, array $invokables): void
    {
        foreach ($invokables as $invokable) {
            $container->factory($invokable, static fn () => new $invokable());
        }
    }

    /**
     * @param array<string, callable|string> $factories
     */
    private function addFactories(ContainerInterface $container, array $factories): void
    {
        foreach ($factories as $name => $factory) {
            $container->factory($name, static function (ContainerInterface $psrContainer) use ($name, $factory) {
                if (\is_string($factory) && class_exists($factory)) {
                    $factory = new $factory();
                }

                /** @var callable(ContainerInterface, string): mixed $factory */

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
            $container->factory($alias, static fn (ContainerInterface $psrContainer) => $psrContainer->get($target));
        }
    }

    /**
     * @param array<string, array<int, callable|string>> $delegators
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
                        if (\is_string($delegator) && class_exists($delegator)) {
                            $delegator = new $delegator();
                        }

                        /** @var callable(ContainerInterface, string, callable): mixed $delegator */

                        return $delegator($psrContainer, $name, static fn () => $previous($psrContainer));
                    }
                );
            }
        }
    }
}

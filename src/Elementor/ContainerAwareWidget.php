<?php

namespace VAF\WP\Framework\Elementor;

use Elementor\Widget_Base;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ContainerAwareWidget extends Widget_Base
{
    private static array $containers = [];
    private bool $dependenciesInitialized = false;

    public static function setContainer(ContainerInterface $container): void
    {
        self::$containers[static::class] = $container;
    }

    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
        $this->initializeDependencies();
    }

    private function initializeDependencies(): void
    {
        if ($this->dependenciesInitialized) {
            return;
        }

        $this->dependenciesInitialized = true;

        if (!method_exists($this, 'injectDependencies')) {
            return;
        }

        $container = self::$containers[static::class] ?? null;
        if (!$container) {
            return;
        }

        try {
            $reflection = new ReflectionMethod($this, 'injectDependencies');
            $parameters = $reflection->getParameters();
            $resolvedParams = [];

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();

                if (!$type instanceof ReflectionNamedType) {
                    continue;
                }

                $serviceName = $type->getName();

                if (!$container->has($serviceName)) {
                    if (!$type->allowsNull()) {
                        throw new \RuntimeException(
                            sprintf(
                                'Service "%s" required by %s::injectDependencies() is not available in the container',
                                $serviceName,
                                static::class
                            )
                        );
                    }
                    $resolvedParams[] = null;
                    continue;
                }

                $resolvedParams[] = $container->get($serviceName);
            }

            $this->injectDependencies(...$resolvedParams);
        } catch (\ReflectionException $e) {
            // Method doesn't exist or is not accessible, ignore
        }
    }
}

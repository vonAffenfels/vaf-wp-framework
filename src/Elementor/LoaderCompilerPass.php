<?php

namespace VAF\WP\Framework\Elementor;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\Elementor\Attribute\AsElementorWidget;

final class LoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('elementor.loader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('elementor.loader');

        $elementorWidgetServices = $container->findTaggedServiceIds('elementor.widget');

        $widgetContainerData = [];
        foreach ($elementorWidgetServices as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);
            $widgetContainerData[$id] = $this->getWidgetContainerData($definition->getClass(), $container);
        }
        $loaderDefinition->setArgument('$widgetContainer', $widgetContainerData);
    }

    private function getWidgetContainerData(string $class, ContainerBuilder $container): array
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->hasMethod('injectDependencies')) {
            return [
                'class' => $class,
                'dependencies' => []
            ];
        }

        $method = $reflection->getMethod('injectDependencies');
        $dependencies = [];

        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType) {
                continue;
            }

            $serviceName = $type->getName();

            if ($container->has($serviceName)) {
                $container->findDefinition($serviceName)->setPublic(true);
                $dependencies[] = $serviceName;
            }
        }

        return [
            'class' => $class,
            'dependencies' => $dependencies
        ];
    }
}

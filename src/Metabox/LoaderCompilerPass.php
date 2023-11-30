<?php

namespace VAF\WP\Framework\Metabox;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\Metabox\Attribute\Metabox;

final class LoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('metabox.loader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('metabox.loader');

        $metaboxContainerServices = $container->findTaggedServiceIds('metabox.container');

        $metaboxContainerData = [];
        foreach ($metaboxContainerServices as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);
            $metaboxContainerData[$id] = $this->getMetaboxContainerData($definition->getClass(), $container);
        }
        $loaderDefinition->setArgument('$metaboxContainer', $metaboxContainerData);
    }

    private function getMetaboxContainerData(string $class, ContainerBuilder $container): array
    {
        $data = [];

        $reflection = new ReflectionClass($class);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $numParameters = $method->getNumberOfParameters();
            $methodName = $method->getName();

            // Check if the Metabox attribute is present
            $attributes = $method->getAttributes(Metabox::class);
            if (empty($attributes)) {
                continue;
            }

            # Check if we have to inject service containers into parameters
            $serviceParams = [];
            foreach ($method->getParameters() as $paramIdx => $parameter) {
                $type = $parameter->getType();
                if (is_null($type)) {
                    continue;
                }

                if ($container->has($type->getName())) {
                    # We found a service parameter
                    # So reduce number of parameters of metabox by one
                    # And register the service parameter

                    $numParameters--;
                    $serviceParams[$paramIdx] = $type->getName();

                    $container->findDefinition($type->getName())->setPublic(true);
                }
            }

            foreach ($attributes as $attribute) {
                /**
                 * @var Metabox $instance
                 */
                $instance = $attribute->newInstance();

                $data[] = [
                    'method' => $methodName,
                    'priority' => $instance->priority,
                    'numParams' => $numParameters,
                    'serviceParams' => $serviceParams
                ];
            }
        }

        return $data;
    }
}

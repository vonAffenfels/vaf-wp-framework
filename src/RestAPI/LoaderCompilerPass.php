<?php

namespace VAF\WP\Framework\RestAPI;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionUnionType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\RestAPI\Attribute\AsRestContainer;
use VAF\WP\Framework\RestAPI\Attribute\RestRoute;

final class LoaderCompilerPass implements CompilerPassInterface
{
    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('restapi.loader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('restapi.loader');

        $restAPIContainerServices = $container->findTaggedServiceIds('restapi.container');

        $containerData = [];
        foreach ($restAPIContainerServices as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);
            $containerData[$id] = $this->getContainerData($definition->getClass(), $container);
        }
        $loaderDefinition->setArgument('$restContainer', $containerData);
    }

    /**
     * @throws Exception
     */
    private function getContainerData(string $class, ContainerBuilder $container): array
    {
        $data = [];

        $reflection = new ReflectionClass($class);
        $attribute = $reflection->getAttributes(AsRestContainer::class);
        if (empty($attribute)) {
            return $data;
        }

        /** @var AsRestContainer $containerAttribute */
        $containerAttribute = $attribute[0]->newInstance();

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            // Check if the Hook attribute is present
            $attribute = $method->getAttributes(RestRoute::class);
            if (empty($attribute)) {
                continue;
            }

            /** @var RestRoute $instance */
            $instance = $attribute[0]->newInstance();

            $serviceParams = [];

            foreach ($method->getParameters() as $parameter) {
                $type = $parameter->getType();
                if ($type instanceof ReflectionIntersectionType || $type instanceof ReflectionUnionType) {
                    throw new Exception(
                        sprintf(
                            'Parameter type for RestRoute "%s"%s can\'t be a union or intersection type!',
                            (!empty($containerAttribute->namespace)) ?
                                sprintf(' of namespace "%s"', $containerAttribute->namespace) :
                                '',
                            $instance->uri
                        )
                    );
                }

                if (!$container->has($type->getName())) {
                    throw new Exception(
                        sprintf(
                            'Parameter type "%s" for RestRoute "%s"%s is not allowed. ' .
                            'Only registered service classes are allowed',
                            $type->getName(),
                            (!empty($containerAttribute->namespace)) ?
                                sprintf(' of namespace "%s"', $containerAttribute->namespace) :
                                '',
                            $instance->uri
                        )
                    );
                }

                $container->findDefinition($type->getName())->setPublic(true);
                $serviceParams[$parameter->getName()] = $type->getName();
            }

            $data[] = [
                'callback' => $methodName,
                'method' => $instance->method,
                'uri' => $instance->uri,
                'namespace' => $containerAttribute->namespace,
                'serviceParams' => $serviceParams
            ];
        }

        return $data;
    }
}

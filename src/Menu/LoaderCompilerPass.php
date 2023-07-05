<?php

namespace VAF\WP\Framework\Menu;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionUnionType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\Menu\Attribute\MenuItem;
use VAF\WP\Framework\Utils\Capabilities;
use VAF\WP\Framework\Utils\Dashicons;

final class LoaderCompilerPass implements CompilerPassInterface
{
    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('menu.loader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('menu.loader');

        $menuContainerServices = $container->findTaggedServiceIds('menu.container');

        $menuData = [
            'top' => [],
            'sub' => []
        ];
        foreach ($menuContainerServices as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);
            $menuContainerData = $this->getMenuContainerData($definition->getClass(), $container);

            $menuData['top'] = array_merge($menuData['top'], $menuContainerData['top']);
            $menuData['sub'] = array_merge($menuData['sub'], $menuContainerData['sub']);
        }
        $loaderDefinition->setArgument('$menuData', $menuData);
    }

    /**
     * @throws Exception
     */
    private function getMenuContainerData(string $class, ContainerBuilder $container): array
    {
        $data = [
            'top' => [],
            'sub' => []
        ];

        $reflection = new ReflectionClass($class);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $numParameters = $method->getNumberOfParameters();
            $methodName = $method->getName();

            // Check if the Hook attribute is present
            $attribute = $method->getAttributes(MenuItem::class);
            if (empty($attribute)) {
                continue;
            }

            /** @var MenuItem $instance */
            $instance = $attribute[0]->newInstance();

            $serviceParams = [];

            foreach ($method->getParameters() as $parameter) {
                $type = $parameter->getType();
                if ($type instanceof ReflectionIntersectionType || $type instanceof ReflectionUnionType) {
                    throw new Exception(
                        sprintf(
                            'Parameter type for menu "%s" can\'t be a union or intersection type!',
                            $instance->slug
                        )
                    );
                }

                if (!$container->has($type->getName())) {
                    throw new Exception(
                        sprintf(
                            'Parameter type "%s" for menu "%s" is not allowed. ' .
                            'Only registered service classes are allowed',
                            $type->getName(),
                            $instance->slug
                        )
                    );
                }

                $container->findDefinition($type->getName())->setPublic(true);
                $serviceParams[$parameter->getName()] = $type->getName();
            }

            $menuData = [
                'method' => $methodName,
                'service' => $class,
                'menuTitle' => $instance->menuTitle,
                'capability' => $instance->capability instanceof Capabilities
                    ? $instance->capability->value
                    : $instance->capability,
                'slug' => $instance->slug,
                'icon' => $instance->icon instanceof Dashicons
                    ? $instance->icon->value
                    : $instance->icon,
                'position' => $instance->position,
                'serviceParams' => $serviceParams,
                'parent' => $instance->parent,
                'subMenuTitle' => $instance->subMenuTitle
            ];

            if (!empty($instance->parent)) {
                $data['sub'][] = $menuData;
            } else {
                $data['top'][] = $menuData;
            }
        }

        return $data;
    }
}

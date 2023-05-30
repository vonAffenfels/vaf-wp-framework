<?php

namespace VAF\WP\Framework\Shortcode;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionUnionType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\Shortcode\Attribute\Shortcode;

final class LoaderCompilerPass implements CompilerPassInterface
{
    private array $allowedTypes = ['int', 'string', 'bool'];

    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('shortcode.loader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('shortcode.loader');

        $shortcodeContainerServices = $container->findTaggedServiceIds('shortcode.container');

        $shortcodeContainerData = [];
        foreach ($shortcodeContainerServices as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);
            $shortcodeContainerData[$id] = $this->getShortcodeContainerData($definition->getClass(), $container);
        }
        $loaderDefinition->setArgument('$shortcodeContainer', $shortcodeContainerData);
    }

    /**
     * @throws Exception
     */
    private function getShortcodeContainerData(string $class, ContainerBuilder $container): array
    {
        $data = [];

        $reflection = new ReflectionClass($class);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            // Check if the Hook attribute is present
            $attribute = $method->getAttributes(Shortcode::class);
            if (empty($attribute)) {
                continue;
            }

            /** @var Shortcode $instance */
            $instance = $attribute[0]->newInstance();

            $params = [];
            $paramsLower = [];
            $paramTypes = [];
            $serviceParams = [];

            foreach ($method->getParameters() as $parameter) {
                $type = $parameter->getType();
                if ($type instanceof ReflectionIntersectionType || $type instanceof ReflectionUnionType) {
                    throw new Exception(
                        sprintf(
                            'Parameter type for shortcode "%s" can\'t be a union or intersection type!',
                            $instance->tag
                        )
                    );
                }

                if (
                    !in_array($type->getName(), $this->allowedTypes)
                    && !$container->has($type->getName())
                ) {
                    throw new Exception(
                        sprintf(
                            'Parameter type "%s" for shortcode "%s" is not allowed. ' .
                            'Only %s or registered service classes are allowed',
                            $type->getName(),
                            $instance->tag,
                            '"' . implode('", "', $this->allowedTypes) . '"'
                        )
                    );
                }

                if (in_array($type->getName(), $this->allowedTypes)) {
                    # Handle internal parameter types
                    # Parameter of those types can be passed as parameter to the shortcode
                    # and have to be optional

                    if (!$parameter->isOptional()) {
                        throw new Exception(
                            sprintf(
                                'Parameter "%s" for shortcode "%s" has to be optional',
                                $parameter->getName(),
                                $instance->tag
                            )
                        );
                    }

                    $name = $parameter->getName();
                    $lowerName = strtolower($name);

                    $params[$lowerName] = $parameter->getDefaultValue();
                    $paramsLower[$lowerName] = $name;
                    $paramTypes[$lowerName] = $type->getName();
                } else {
                    $container->findDefinition($type->getName())->setPublic(true);
                    $serviceParams[$parameter->getName()] = $type->getName();
                }
            }

            $data[$instance->tag] = [
                'method' => $methodName,
                'params' => $params,
                'paramsLower' => $paramsLower,
                'paramTypes' => $paramTypes,
                'serviceParams' => $serviceParams
            ];
        }

        return $data;
    }
}

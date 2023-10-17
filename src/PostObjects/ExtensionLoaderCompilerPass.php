<?php

namespace VAF\WP\Framework\PostObjects;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionUnionType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\PostObjects\Attributes\Field;
use VAF\WP\Framework\PostObjects\Attributes\PostTypeExtension;
use VAF\WP\Framework\System\Parameters\Parameter;
use VAF\WP\Framework\System\Parameters\ParameterBag;

class ExtensionLoaderCompilerPass implements CompilerPassInterface
{
    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('postobject.extensionLoader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('postobject.extensionLoader');

        $postObjectExtionsServices = $container->findTaggedServiceIds('postobject.extension');

        $extensions = [];
        foreach ($postObjectExtionsServices as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);
            $extensions = array_merge($extensions, $this->getExtensionData($definition->getClass(), $container));
        }
        $loaderDefinition->setArgument('$extensions', $extensions);
    }

    /**
     * @throws Exception
     */
    private function getExtensionData(string $class, ContainerBuilder $container): array
    {
        $extensions = [];

        $reflection = new ReflectionClass($class);
        $attribute = $reflection->getAttributes(PostTypeExtension::class);
        if (empty($attribute)) {
            return $extensions;
        }

        /** @var PostTypeExtension $extensionObj */
        $extensionObj = $attribute[0]->newInstance();
        $postTypes = $extensionObj->postTypes;
        if (empty($postTypes)) {
            $postTypes = ['all'];
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Check if the Field attribute is present
            $attribute = $method->getAttributes(Field::class);
            if (empty($attribute)) {
                continue;
            }

            /** @var Field $attrInstance */
            $attrInstance = $attribute[0]->newInstance();

            $methodName = $method->getName();
            $fieldName = $attrInstance->fieldName;

            $parameterBag = new ParameterBag();

            foreach ($method->getParameters() as $parameter) {
                $type = $parameter->getType();
                if ($type instanceof ReflectionIntersectionType || $type instanceof ReflectionUnionType) {
                    throw new Exception(
                        sprintf(
                            'Parameter type for PostObjectExtension "%s" (field "%s") ' .
                            'can\'t be a union or intersection type!',
                            $class,
                            $fieldName
                        )
                    );
                }

                if ($type->getName() !== PostObject::class && !$container->has($type->getName())) {
                    throw new Exception(
                        sprintf(
                            'Parameter type "%s" for PostObjectExtension "%s" (field "%s") is not allowed. ' .
                            'Only %s or registered service classes are allowed',
                            $type->getName(),
                            $class,
                            $fieldName,
                            PostObject::class
                        )
                    );
                }

                $isServiceParam = false;
                if ($container->has($type->getName())) {
                    $container->findDefinition($type->getName())->setPublic(true);
                    $isServiceParam = true;
                }

                $parameterBag->addParam(
                    new Parameter(
                        name: $parameter->getName(),
                        type: $type->getName(),
                        isOptional: $parameter->isOptional(),
                        default: $parameter->isOptional() ? $parameter->getDefaultValue() : null,
                        isServiceParam: $isServiceParam,
                        isNullable: $parameter->allowsNull()
                    )
                );
            }

            $extensions[] = [
                'method' => $methodName,
                'postTypes' => $postTypes,
                'fieldName' => $fieldName,
                'serviceId' => $class,
                'params' => $parameterBag->toArray()
            ];
        }

        return $extensions;
    }
}

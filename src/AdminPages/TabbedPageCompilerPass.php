<?php

namespace VAF\WP\Framework\AdminPages;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionUnionType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\AdminPages\Attributes\IsTabbedPage;
use VAF\WP\Framework\AdminPages\Attributes\PageTab;
use VAF\WP\Framework\System\Parameters\Parameter;
use VAF\WP\Framework\System\Parameters\ParameterBag;

final class TabbedPageCompilerPass implements CompilerPassInterface
{
    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container): void
    {
        $tabbedPages = $container->findTaggedServiceIds('adminpages.tabbed');

        foreach ($tabbedPages as $id => $tags) {
            $definition = $container->findDefinition($id);

            $classReflection = new ReflectionClass($definition->getClass());
            $attribute = $classReflection->getAttributes(IsTabbedPage::class);
            if (empty($attribute)) {
                continue;
            }

            /** @var IsTabbedPage $attrInstance */
            $attrInstance = $attribute[0]->newInstance();

            $handlerMethods = [];
            foreach ($classReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                // Check if the Hook attribute is present
                $attribute = $method->getAttributes(PageTab::class);
                if (empty($attribute)) {
                    continue;
                }

                /** @var PageTab $instance */
                $instance = $attribute[0]->newInstance();

                $slug = $instance->slug;
                $title = $instance->title;

                $parameterBag = new ParameterBag();

                foreach ($method->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type instanceof ReflectionIntersectionType || $type instanceof ReflectionUnionType) {
                        throw new Exception(
                            sprintf(
                                'Parameter type for TabbedPage Tab "%s" can\'t be a union or intersection type!',
                                $slug
                            )
                        );
                    }

                    if (!$container->has($type->getName())) {
                        throw new Exception(
                            sprintf(
                                'Parameter type "%s" for TabbedPage Tab "%s" is not allowed. ' .
                                'Only registered service classes are allowed',
                                $type->getName(),
                                $slug
                            )
                        );
                    }

                    if ($container->has($type->getName())) {
                        $container->findDefinition($type->getName())->setPublic(true);
                    }

                    $parameterBag->addParam(
                        new Parameter(
                            name: $parameter->getName(),
                            type: $type->getName(),
                            isOptional: $parameter->isOptional(),
                            default: $parameter->isOptional() ? $parameter->getDefaultValue() : null,
                            isServiceParam: true
                        )
                    );
                }

                $handlerMethods[$slug] = [
                    'params' => $parameterBag->toArray(),
                    'slug' => $slug,
                    'title' => $title,
                    'method' => $method->getName()
                ];
            }

            $definition->setArgument('$handler', $handlerMethods);
            $definition->setArgument('$pageTitle', $attrInstance->pageTitle);
            $definition->setArgument('$pageVar', $attrInstance->pageVar);
            $definition->setArgument('$defaultSlug', $attrInstance->defaultSlug);
        }
    }
}

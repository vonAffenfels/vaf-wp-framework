<?php

namespace VAF\WP\Framework\BulkEdit;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\BulkEdit\Attribute\BulkEdit;
use VAF\WP\Framework\Slug;

final class LoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('bulkedit.loader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('bulkedit.loader');

        $bulkeditContainerServices = $container->findTaggedServiceIds('bulkedit.container');

        $bulkeditContainerData = [];
        foreach ($bulkeditContainerServices as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);
            $bulkeditContainerData[$id] = $this->getBulkEditContainerData($definition->getClass(), $container);
        }
        $loaderDefinition->setArgument('$bulkeditContainer', $bulkeditContainerData);
    }

    private function getBulkEditContainerData(string $class, ContainerBuilder $container): array
    {
        $data = [];

        $reflection = new ReflectionClass($class);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            // Check if the BulkEdit attribute is present
            $attributes = $method->getAttributes(BulkEdit::class);
            if (empty($attributes)) {
                continue;
            }

            foreach ($attributes as $attribute) {
                /**
                 * @var BulkEdit $instance
                 */
                $instance = $attribute->newInstance();

                $data[] = [
                    'method' => $methodName,
                    'name' => (string)Slug::fromName($instance->title),
                    'title' => $instance->title,
                    'postTypes' => $instance->postTypes,
                    'supporting' => $instance->supporting,
                ];
            }
        }

        return $data;
    }
}

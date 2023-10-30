<?php

namespace VAF\WP\Framework\GutenbergBlock;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\GutenbergBlock\Attribute\GutenbergBlock;
use VAF\WP\Framework\GutenbergBlock\Attribute\Hook;

final class LoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('gutenbergblock.loader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('gutenbergblock.loader');

        $gutenbergBlocks = $container->findTaggedServiceIds('gutenbergblock.block');

        $gutenbergBlocks = [];
        foreach ($gutenbergBlocks as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);
            $gutenbergBlocks[$id] = $this->gutenbergBlockContainerData($definition->getClass(), $container);
        }
        $loaderDefinition->setArgument('$gutenbergBlockContainer', $gutenbergBlocks);
    }

    private function gutenbergBlockContainerData($class, ContainerBuilder $container)
    {
        $reflection = new ReflectionClass($class);
        /**
         * @var GutenbergBlock $attribute
         */
        $attribute = $reflection->getAttributes(GutenbergBlock::class)[0];

        return [
            'block_type' => $attribute->type,
            'api_version' => $attribute->apiVersion,
            'editor_script' => $attribute->editorScript,
        ];
    }

}

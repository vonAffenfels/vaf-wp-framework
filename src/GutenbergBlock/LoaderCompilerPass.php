<?php

namespace VAF\WP\Framework\GutenbergBlock;

use LogicException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\GutenbergBlock\Attribute\AsDynamicBlock;

final class LoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('gutenbergblock.loader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('gutenbergblock.loader');

        $dynamicBlockServicess = $container->findTaggedServiceIds('gutenbergblock.dynamicblock');

        $dynamicBlockDefinitions = [];
        foreach ($dynamicBlockServicess as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);
            try {
                $dynamicBlockDefinitions[$id] = $this->getDynamicBlockDefinition($definition->getClass(), $container);
            } catch(NoRenderMethodException) {
            }
        }
        $loaderDefinition->setArgument('$dynamicBlocks', $dynamicBlockDefinitions);
    }

    private function getDynamicBlockDefinition(string $class, ContainerBuilder $container): array
    {
        $reflection = new ReflectionClass($class);
        if(!$reflection->hasMethod('render')) {
            throw new NoRenderMethodException();
        }

        if( empty($reflection->getAttributes(AsDynamicBlock::class)) ) {
            throw new LogicException("DynamicBlock without AsDynamicBlock Attribute - should be impossible");
        }

        $attribute = $reflection->getAttributes(AsDynamicBlock::class)[0]->newInstance();
        return [
            'type' => $attribute->blockType,
            'class' => $class,
            'renderer' => RendererDefinition::fromClassReflection($reflection)->definition(),
            'options' => [
                'api_version' => $attribute->apiVersion,
                'editor_script' => $attribute->editorScriptHandle,
            ]
        ];
    }

}

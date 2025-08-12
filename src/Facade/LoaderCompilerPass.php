<?php

namespace VAF\WP\Framework\Facade;

use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\Facade\Attribute\AsFacade;

final class LoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('facade.loader')) {
            return;
        }

        $loaderDefinition = $container->findDefinition('facade.loader');
        $facadeServices = $container->findTaggedServiceIds('facade.container');

        $facadeData = [];
        foreach ($facadeServices as $id => $tags) {
            $definition = $container->findDefinition($id);
            $facadeClass = $definition->getClass();
            
            $reflection = new ReflectionClass($facadeClass);
            $attributes = $reflection->getAttributes(AsFacade::class);
            
            if (empty($attributes)) {
                continue;
            }
            
            $attribute = $attributes[0]->newInstance();
            $accessorClass = $attribute->facadeAccessor;
            
            // Ensure the accessor service is public
            if ($container->has($accessorClass)) {
                $container->findDefinition($accessorClass)->setPublic(true);
            }
            
            // Override getFacadeAccessor method
            $facadeData[$facadeClass] = [
                'accessor' => $accessorClass,
                'alias' => $this->generateAlias($facadeClass)
            ];
        }
        
        $loaderDefinition->setArgument('$facades', $facadeData);
    }
    
    private function generateAlias(string $facadeClass): string
    {
        $parts = explode('\\', $facadeClass);
        $className = end($parts);
        
        // Remove 'Facade' suffix if present
        if (str_ends_with($className, 'Facade')) {
            $className = substr($className, 0, -6);
        }
        
        return $className;
    }
}
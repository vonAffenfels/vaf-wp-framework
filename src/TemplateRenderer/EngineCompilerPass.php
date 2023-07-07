<?php

namespace VAF\WP\Framework\TemplateRenderer;

use Exception;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\TemplateRenderer\Attribute\AsTemplateEngine;

final class EngineCompilerPass implements CompilerPassInterface
{
    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(TemplateRenderer::class)) {
            return;
        }

        $templateRendererDefinition = $container->findDefinition(TemplateRenderer::class);
        $templateEngines = $container->findTaggedServiceIds('template.engine');

        $registeredEngines = [];

        foreach ($templateEngines as $id => $tags) {
            $definition = $container->findDefinition($id);
            $definition->setPublic(true);

            $extension = $this->getEngineExtension($definition->getClass(), $container);
            if (is_null($extension)) {
                continue;
            }

            $registeredEngines[$extension] = $id;
        }
        $templateRendererDefinition->setArgument('$engines', $registeredEngines);
    }

    /**
     * @throws Exception
     */
    private function getEngineExtension(string $class, ContainerBuilder $container): ?string
    {
        $reflection = new ReflectionClass($class);
        // Check if the Hook attribute is present
        $attribute = $reflection->getAttributes(AsTemplateEngine::class);
        if (empty($attribute)) {
            return null;
        }

        /** @var AsTemplateEngine $instance */
        $instance = $attribute[0]->newInstance();

        return $instance->extension;
    }
}

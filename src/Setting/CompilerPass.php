<?php

namespace VAF\WP\Framework\Setting;

use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\Setting\Attribute\AsSettingContainer;

final class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $settingContainerServices = $container->findTaggedServiceIds('setting.container');

        foreach ($settingContainerServices as $id => $tags) {
            $definition = $container->findDefinition($id);

            $reflection = new ReflectionClass($definition->getClass());
            $attributes = $reflection->getAttributes(AsSettingContainer::class);
            if (empty($attributes)) {
                continue;
            }

            /** @var AsSettingContainer $attrInstance */
            $attrInstance = $attributes[0]->newInstance();

            $definition->setArgument('$name', $attrInstance->name);
            // Inject the base name from container parameter instead of BaseWordpress instance
            $definition->setArgument('$baseName', '%wordpress.base.name%');
            $definition->setArgument('$default', $attrInstance->default);
        }
    }
}

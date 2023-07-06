<?php

namespace VAF\WP\Framework\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use VAF\WP\Framework\Plugin;

class PluginKernel extends WordpressKernel
{
    protected function bootHandler(): void
    {
        $this->getContainer()->set('plugin', $this->base);

        parent::bootHandler();
    }

    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void {
        if (!$builder->hasDefinition('plugin')) {
            $builder->register('plugin', $this->base::class)
                ->setAutoconfigured(true)
                ->setSynthetic(true)
                ->setPublic(true);
        }

        $builder->addObjectResource($this->base);
        $builder->setAlias($this->base::class, 'plugin')->setPublic(true);

        // Register all parent classes of plugin as aliases
        foreach (class_parents($this->base) as $parent) {
            if (!$builder->hasAlias($parent)) {
                $builder->setAlias($parent, 'plugin');
            }
        }

        parent::configureContainer($container, $loader, $builder);
    }
}

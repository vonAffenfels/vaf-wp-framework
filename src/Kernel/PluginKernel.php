<?php

namespace VAF\WP\Library\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use VAF\WP\Library\Plugin;

class PluginKernel extends WordpressKernel
{
    public function __construct(string $projectDir, bool $debug, private readonly Plugin $plugin)
    {
        parent::__construct($projectDir, $debug);
    }

    protected function bootHandler(): void
    {
        $this->getContainer()->set('plugin', $this->plugin);

        parent::bootHandler();
    }

    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void {
        if (!$builder->hasDefinition('plugin')) {
            $builder->register('plugin', $this->plugin::class)
                ->setAutoconfigured(true)
                ->setSynthetic(true)
                ->setPublic(true);
        }

        $builder->addObjectResource($this->plugin);
        $builder->setAlias($this->plugin::class, 'plugin')->setPublic(true);

        // Register all parent classes of plugin as aliases
        foreach (class_parents($this->plugin) as $parent) {
            if (!$builder->hasAlias($parent)) {
                $builder->setAlias($parent, 'plugin');
            }
        }

        parent::configureContainer($container, $loader, $builder);
    }
}

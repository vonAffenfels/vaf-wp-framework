<?php

namespace VAF\WP\Framework\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use VAF\WP\Framework\NavMenus\Attributes\NavMenu;
use VAF\WP\Framework\NavMenus\Loader as NavMenusLoader;
use VAF\WP\Framework\NavMenus\LoaderCompilerPass as NavMenusLoaderCompilerPassAlias;

class ThemeKernel extends WordpressKernel
{
    protected function bootHandler(): void
    {
        $this->initializeContainer();
        $this->getContainer()->set('theme', $this->base);

        add_filter('after_setup_theme', function (): void {
            /** @var NavMenusLoader $loader */
            $loader = $this->getContainer()->get(NavMenusLoader::class);
            $loader->registerNavMenus();
        });

        parent::bootHandler();
    }

    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void {
        if (!$builder->hasDefinition('theme')) {
            $builder->register('theme', $this->base::class)
                ->setAutoconfigured(true)
                ->setSynthetic(true)
                ->setPublic(true);
        }

        $builder->addObjectResource($this->base);
        $builder->setAlias($this->base::class, 'theme')->setPublic(true);

        // Register all parent classes of plugin as aliases
        foreach (class_parents($this->base) as $parent) {
            if (!$builder->hasAlias($parent)) {
                $builder->setAlias($parent, 'theme');
            }
        }

        parent::configureContainer($container, $loader, $builder);

        $this->registerNavMenus($builder);
    }

    private function registerNavMenus(ContainerBuilder $builder): void
    {
        $builder->register(NavMenusLoader::class, NavMenusLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new NavMenusLoaderCompilerPassAlias());

        $builder->registerAttributeForAutoconfiguration(
            NavMenu::class,
            static function (
                ChildDefinition $defintion
            ): void {
                $defintion->addTag('navmenus.menu');
            }
        );
    }
}

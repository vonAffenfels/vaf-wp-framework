<?php

namespace VAF\WP\Framework\Kernel;

use Exception;
use ReflectionClass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use VAF\WP\Framework\Hook\Attribute\AsHookContainer;
use VAF\WP\Framework\Hook\Loader as HookLoader;
use VAF\WP\Framework\Hook\LoaderCompilerPass as HookLoaderCompilerPass;
use VAF\WP\Framework\Shortcode\Attribute\AsShortcodeContainer;
use VAF\WP\Framework\Shortcode\Loader as ShortcodeLoader;
use VAF\WP\Framework\Shortcode\LoaderCompilerPass as ShortcodeLoaderCompilerPass;

abstract class WordpressKernel extends Kernel
{
    protected function bootHandler(): void
    {
        /** @var HookLoader $hookLoader */
        $hookLoader = $this->getContainer()->get('hook.loader');
        $hookLoader->registerHooks();

        /** @var ShortcodeLoader $shortcodeLoader */
        $shortcodeLoader = $this->getContainer()->get('shortcode.loader');
        $shortcodeLoader->registerShortcodes();
    }

    /**
     * Configures the container.
     *
     * You can register services:
     *
     *     $container->services()->set('halloween', 'FooBundle\HalloweenProvider');
     *
     * Or parameters:
     *
     *     $container->parameters()->set('halloween', 'lot of fun');
     */
    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void {
        $configDir = $this->getConfigDir();

        if (is_file($configDir . '/services.yaml')) {
            $container->import($configDir . '/services.yaml');
        } else {
            $container->import($configDir . '/{services}.php');
        }

        $this->registerHookContainer($builder);
        $this->registerShortcodeContainer($builder);
    }

    /**
     * Gets the path to the configuration directory.
     */
    private function getConfigDir(): string
    {
        return $this->getProjectDir() . '/config';
    }

    private function registerShortcodeContainer(ContainerBuilder $builder): void
    {
        $builder->register('shortcode.loader', ShortcodeLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new ShortcodeLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsShortcodeContainer::class,
            static function (
                ChildDefinition $defintion,
                AsShortcodeContainer $attribute,
                ReflectionClass $reflector
            ): void {
                $defintion->addTag('shortcode.container');
            }
        );
    }

    private function registerHookContainer(ContainerBuilder $builder): void
    {
        $builder->register('hook.loader', HookLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new HookLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsHookContainer::class,
            static function (
                ChildDefinition $defintion,
                AsHookContainer $attribute,
                ReflectionClass $reflector
            ): void {
                $defintion->addTag('hook.container');
            }
        );
    }
}

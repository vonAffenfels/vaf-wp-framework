<?php

namespace VAF\WP\Framework\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\Hook\Attribute\AsHookContainer;
use VAF\WP\Framework\Hook\Loader as HookLoader;
use VAF\WP\Framework\Hook\LoaderCompilerPass as HookLoaderCompilerPass;
use VAF\WP\Framework\Menu\Attribute\AsMenuContainer;
use VAF\WP\Framework\Menu\Loader as MenuLoader;
use VAF\WP\Framework\Menu\LoaderCompilerPass as MenuLoaderCompilerPass;
use VAF\WP\Framework\Request;
use VAF\WP\Framework\RestAPI\Attribute\AsRestContainer;
use VAF\WP\Framework\RestAPI\Loader as RestAPILoader;
use VAF\WP\Framework\RestAPI\LoaderCompilerPass as RestAPILoaderCompilerPass;
use VAF\WP\Framework\Setting\Attribute\AsSettingContainer;
use VAF\WP\Framework\Setting\CompilerPass as SettingCompilerpass;
use VAF\WP\Framework\Shortcode\Attribute\AsShortcodeContainer;
use VAF\WP\Framework\Shortcode\Loader as ShortcodeLoader;
use VAF\WP\Framework\Shortcode\LoaderCompilerPass as ShortcodeLoaderCompilerPass;
use VAF\WP\Framework\Template\Attribute\IsTemplate;
use VAF\WP\Framework\Template\Attribute\UseScript;
use VAF\WP\Framework\TemplateRenderer\Attribute\AsTemplateEngine;
use VAF\WP\Framework\TemplateRenderer\Engine\PHTMLEngine;
use VAF\WP\Framework\TemplateRenderer\EngineCompilerPass;
use VAF\WP\Framework\TemplateRenderer\TemplateRenderer;
use VAF\WP\Framework\Utils\Templates\Notice;
use WP;

abstract class WordpressKernel extends Kernel
{
    private const QUERY_VAR_JS = '_vaf_js';
    private const QUERY_VAR_CSS = '_vaf_css';

    public function __construct(string $projectDir, bool $debug, protected readonly BaseWordpress $base)
    {
        parent::__construct($projectDir, $debug);
    }

    protected function bootHandler(): void
    {
        /** @var HookLoader $hookLoader */
        $hookLoader = $this->getContainer()->get('hook.loader');
        $hookLoader->registerHooks();

        /** @var ShortcodeLoader $shortcodeLoader */
        $shortcodeLoader = $this->getContainer()->get('shortcode.loader');
        $shortcodeLoader->registerShortcodes();

        // Registering REST routes
        add_action('rest_api_init', function () {
            /** @var RestAPILoader $restApiLoader */
            $restApiLoader = $this->getContainer()->get('restapi.loader');
            $restApiLoader->registerRestRoutes();
        });

        add_action('admin_menu', function () {
            /** @var MenuLoader $menuLoader */
            $menuLoader = $this->getContainer()->get('menu.loader');
            $menuLoader->registerMenus();
        });
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

        $this->registerRequestService($builder);
        $this->registerTemplateRenderer($builder);
        $this->registerTemplate($builder);

        $this->registerHookContainer($builder);
        $this->registerShortcodeContainer($builder);
        $this->registerSettingsContainer($builder);
        $this->registerRestAPIContainer($builder);
        $this->registerMenuContainer($builder);

        $this->base->configureContainer($builder);
    }

    /**
     * Gets the path to the configuration directory.
     */
    private function getConfigDir(): string
    {
        return $this->getProjectDir() . '/config';
    }

    private function registerRequestService(ContainerBuilder $builder): void
    {
        $builder->register(Request::class, Request::class)
            ->setPublic(true)
            ->setAutowired(true);
    }

    private function registerTemplate(ContainerBuilder $builder): void
    {
        $builder->registerAttributeForAutoconfiguration(
            IsTemplate::class,
            static function (
                ChildDefinition $definition,
                IsTemplate $attribute
            ): void {
                $definition->setArgument('$templateFile', $attribute->templateFile);
            }
        );

        $builder->registerAttributeForAutoconfiguration(
            UseScript::class,
            static function (
                ChildDefinition $definition,
                UseScript $attribute
            ): void {
                $definition->addMethodCall('addScript', [
                    '$src' => $attribute->src,
                    '$deps' => $attribute->deps
                ]);
            }
        );

        $builder->register(Notice::class, Notice::class)
            ->setAutoconfigured(true)
            ->setAutowired(true);
        $builder->setAlias('template.notice', Notice::class)
            ->setPublic(true);
    }

    private function registerTemplateRenderer(ContainerBuilder $builder): void
    {
        $builder->register(TemplateRenderer::class, TemplateRenderer::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->register(PHTMLEngine::class, PHTMLEngine::class)
            ->setPublic(true)
            ->setAutowired(true)
            ->addTag('template.engine');

        $builder->addCompilerPass(new EngineCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsTemplateEngine::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('template.engine');
            }
        );
    }

    private function registerSettingsContainer(ContainerBuilder $builder): void
    {
        $builder->addCompilerPass(new SettingCompilerpass());

        $builder->registerAttributeForAutoconfiguration(
            AsSettingContainer::class,
            static function (
                ChildDefinition $defintion
            ): void {
                $defintion->addTag('setting.container');
            }
        );
    }

    private function registerMenuContainer(ContainerBuilder $builder): void
    {
        $builder->register('menu.loader', MenuLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new MenuLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsMenuContainer::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('menu.container');
            }
        );
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
                ChildDefinition $defintion
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
                ChildDefinition $defintion
            ): void {
                $defintion->addTag('hook.container');
            }
        );
    }

    private function registerRestAPIContainer(ContainerBuilder $builder): void
    {
        $builder->register('restapi.loader', RestAPILoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new RestAPILoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsRestContainer::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('restapi.container');
            }
        );
    }
}

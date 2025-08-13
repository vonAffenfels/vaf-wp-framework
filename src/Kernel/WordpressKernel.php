<?php

namespace VAF\WP\Framework\Kernel;

use ReflectionClass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use VAF\WP\Framework\AdminAjax\Attributes\AsAdminAjaxContainer;
use VAF\WP\Framework\AdminAjax\Loader as AdminAjaxLoader;
use VAF\WP\Framework\AdminAjax\LoaderCompilerPass as AdminAjaxLoaderCompilerPass;
use VAF\WP\Framework\AdminPages\Attributes\IsTabbedPage;
use VAF\WP\Framework\AdminPages\TabbedPageCompilerPass;
use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\BulkEdit\Attribute\AsBulkEditContainer;
use VAF\WP\Framework\QuickEdit\Attribute\AsQuickEditContainer;
use VAF\WP\Framework\CustomColumn\Attribute\AsCustomColumnContainer;
use VAF\WP\Framework\GutenbergBlock\Attribute\AsDynamicBlock;
use VAF\WP\Framework\GutenbergBlock\Loader as GutenbergBlockLoader;
use VAF\WP\Framework\GutenbergBlock\LoaderCompilerPass as GutenbergBlockCompilerPass;
use VAF\WP\Framework\Hook\Attribute\AsHookContainer;
use VAF\WP\Framework\Hook\Loader as HookLoader;
use VAF\WP\Framework\Hook\LoaderCompilerPass as HookLoaderCompilerPass;
use VAF\WP\Framework\Menu\Attribute\AsMenuContainer;
use VAF\WP\Framework\Menu\Loader as MenuLoader;
use VAF\WP\Framework\Menu\LoaderCompilerPass as MenuLoaderCompilerPass;
use VAF\WP\Framework\Metabox\Attribute\AsMetaboxContainer;
use VAF\WP\Framework\Metabox\Loader as MetaboxLoader;
use VAF\WP\Framework\Metabox\LoaderCompilerPass as MetaboxLoaderCompilerPass;
use VAF\WP\Framework\BulkEdit\Loader as BulkeditLoader;
use VAF\WP\Framework\BulkEdit\LoaderCompilerPass as BulkeditLoaderCompilerPass;
use VAF\WP\Framework\QuickEdit\Loader as QuickeditLoader;
use VAF\WP\Framework\QuickEdit\LoaderCompilerPass as QuickeditLoaderCompilerPass;
use VAF\WP\Framework\CustomColumn\Loader as CustomColumnLoader;
use VAF\WP\Framework\CustomColumn\LoaderCompilerPass as CustomColumnLoaderCompilerPass;
use VAF\WP\Framework\Facade\Attribute\AsFacade;
use VAF\WP\Framework\Facade\Loader as FacadeLoader;
use VAF\WP\Framework\Facade\LoaderCompilerPass as FacadeLoaderCompilerPass;
use VAF\WP\Framework\Paths\Paths;
use VAF\WP\Framework\PostObjects\Attributes\PostType;
use VAF\WP\Framework\PostObjects\Attributes\PostTypeExtension;
use VAF\WP\Framework\PostObjects\ExtensionLoader as PostObjectExtensionLoader;
use VAF\WP\Framework\PostObjects\ExtensionLoaderCompilerPass as PostObjectExtensionLoaderCompilerPass;
use VAF\WP\Framework\PostObjects\PostObjectManager;
use VAF\WP\Framework\PostObjects\PostTypeLoader;
use VAF\WP\Framework\PostObjects\PostTypes\NavMenuItem;
use VAF\WP\Framework\PostObjects\PostTypes\Page;
use VAF\WP\Framework\PostObjects\PostTypes\Post;
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
use VAF\WP\Framework\Template\Attribute\UseAdminAjax;
use VAF\WP\Framework\Template\Attribute\UseScript;
use VAF\WP\Framework\TemplateRenderer\Attribute\AsFunctionContainer;
use VAF\WP\Framework\TemplateRenderer\Attribute\AsTemplateEngine;
use VAF\WP\Framework\TemplateRenderer\Engine\PHTMLEngine;
use VAF\WP\Framework\TemplateRenderer\Engine\TwigRenderer\Extension;
use VAF\WP\Framework\TemplateRenderer\Engine\TwigRenderer\FileLoader;
use VAF\WP\Framework\TemplateRenderer\Engine\TwigEngine;
use VAF\WP\Framework\TemplateRenderer\EngineCompilerPass;
use VAF\WP\Framework\TemplateRenderer\FunctionCompilerPass;
use VAF\WP\Framework\TemplateRenderer\FunctionHandler;
use VAF\WP\Framework\TemplateRenderer\Functions\BuiltIn\Wordpress;
use VAF\WP\Framework\TemplateRenderer\GlobalContext;
use VAF\WP\Framework\TemplateRenderer\NamespaceHandler;
use VAF\WP\Framework\TemplateRenderer\TemplateRenderer;
use VAF\WP\Framework\Utils\Templates\Admin\Notice;
use VAF\WP\Framework\Utils\Templates\Admin\ReactTemplate;
use VAF\WP\Framework\Utils\Templates\Admin\TabbedPage as TabbedPageTemplate;

abstract class WordpressKernel extends Kernel
{
    public function __construct(
        string $projectDir,
        bool $debug,
        string $namespace,
        protected readonly BaseWordpress $base
    ) {
        parent::__construct($projectDir, $debug, $namespace);
    }

    protected function bootHandler(): void
    {
        /** @var FacadeLoader $facadeLoader */
        $facadeLoader = $this->getContainer()->get('facade.loader');
        $facadeLoader->registerFacades();

        /** @var HookLoader $hookLoader */
        $hookLoader = $this->getContainer()->get('hook.loader');
        $hookLoader->registerHooks();

        /** @var MetaboxLoader $metaboxLoader */
        $metaboxLoader = $this->getContainer()->get('metabox.loader');
        $metaboxLoader->registerMetaboxes();

        /** @var BulkEditLoader $bulkeditLoader */
        $bulkeditLoader = $this->getContainer()->get('bulkedit.loader');
        $bulkeditLoader->registerBulkEditFields();

        /** @var QuickEditLoader $quickeditLoader */
        $quickeditLoader = $this->getContainer()->get('quickedit.loader');
        $quickeditLoader->registerQuickEditFields();

        /** @var CustomColumnLoader $quickeditLoader */
        $customColumnLoader = $this->getContainer()->get('customColumn.loader');
        $customColumnLoader->registerCustomColumnFields();

        /** @var GutenbergBlockLoader $gutenbergBlockLoader */
        $gutenbergBlockLoader = $this->getContainer()->get('gutenbergblock.loader');
        $gutenbergBlockLoader->registerBlocks();

        /** @var ShortcodeLoader $shortcodeLoader */
        $shortcodeLoader = $this->getContainer()->get('shortcode.loader');
        $shortcodeLoader->registerShortcodes();

        /** @var AdminAjaxLoader $adminAjaxLoader */
        $adminAjaxLoader = $this->getContainer()->get('adminajax.loader');
        $adminAjaxLoader->registerAdminAjaxActions();

        /** @var PostObjectExtensionLoader $extensionLoader */
        $extensionLoader = $this->getContainer()->get('postobject.extensionLoader');
        $extensionLoader->registerPostObjectExtensions();

        /** @var PostTypeLoader $postTypeLoader */
        $postTypeLoader = $this->getContainer()->get(PostTypeLoader::class);
        $postTypeLoader->registerPostTypes();

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
        } elseif (is_file($configDir . '/services.php')) {
            $container->import($configDir . '/services.php');
        }

        $this->registerRequestService($builder);
        $this->registerTemplateRenderer($builder);
        $this->registerTemplate($builder);

        $this->registerFacadeContainer($builder);
        $this->registerPathsContainer($builder);
        $this->registerHookContainer($builder);
        $this->registerMetaboxContainer($builder);
        $this->registerBulkeditContainer($builder);
        $this->registerQuickeditContainer($builder);
        $this->registerCustomColumnContainer($builder);
        $this->registerGutenbergBlock($builder);
        $this->registerShortcodeContainer($builder);
        $this->registerSettingsContainer($builder);
        $this->registerRestAPIContainer($builder);
        $this->registerMenuContainer($builder);
        $this->registerAdminPages($builder);

        $this->registerAdminAjaxContainer($builder);

        $this->registerPostObjects($builder);

        $builder->register(ReactTemplate::class, ReactTemplate::class)
            ->setAutoconfigured(true)
            ->setAutowired(true)
        ;
        $this->base->configureContainer($builder, $container);
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

    private function registerPostObjects(ContainerBuilder $builder): void
    {
        $builder->register('postobject.extensionLoader', PostObjectExtensionLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new PostObjectExtensionLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            PostTypeExtension::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('postobject.extension');
            }
        );

        $loaderDefinition = $builder->register(PostTypeLoader::class, PostTypeLoader::class)
            ->setArgument('$postTypes', [])
            ->setPublic(true)
            ->setAutowired(true);

        $builder->registerAttributeForAutoconfiguration(
            PostType::class,
            static function (
                ChildDefinition $definition,
                PostType $attribute,
                ReflectionClass $reflectionClass
            ) use ($loaderDefinition): void {
                $postTypes = $loaderDefinition->getArgument('$postTypes');
                $postTypes[$attribute->postType] = $reflectionClass->getName();
                $loaderDefinition->replaceArgument('$postTypes', $postTypes);

                $definition->setPublic(true);
                $definition->setShared(false);
                $definition->setAutoconfigured(true);
                $definition->setAutowired(true);
            }
        );

        $builder->register(Page::class, Page::class)
            ->setAutoconfigured(true)
            ->setAutowired(true);
        $builder->register(Post::class, Post::class)
            ->setAutoconfigured(true)
            ->setAutowired(true);
        $builder->register(NavMenuItem::class, NavMenuItem::class)
            ->setAutoconfigured(true)
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
                    '$deps' => $attribute->deps,
                    '$adminAjaxActions' => $attribute->adminAjaxActions
                ]);
            }
        );

        $builder->registerAttributeForAutoconfiguration(
            UseAdminAjax::class,
            static function (
                ChildDefinition $definition,
                UseAdminAjax $attribute
            ): void {
                $definition->addMethodCall('registerAdminAjaxAction', [
                    '$action' => $attribute->actionName
                ]);
            }
        );

        $builder->register(Notice::class, Notice::class)
            ->setAutoconfigured(true)
            ->setAutowired(true);
        $builder->setAlias('template.notice', Notice::class)
            ->setPublic(true);
        $builder->register(GlobalContext::class, GlobalContext::class)
            ->setPublic(true);
    }

    private function registerTemplateRenderer(ContainerBuilder $builder): void
    {
        // Register handler
        $builder->register(NamespaceHandler::class, NamespaceHandler::class)
            ->setAutowired(true);
        $builder->register(FunctionHandler::class, FunctionHandler::class)
            ->setAutowired(true);

        // Register built in template functions
        $builder->register(Wordpress::class, Wordpress::class)
            ->setAutowired(true)
            ->addTag('template.functions');

        $builder->register(TemplateRenderer::class, TemplateRenderer::class)
            ->setPublic(true)
            ->setAutowired(true);
        $builder->setAlias('template.renderer', TemplateRenderer::class)
            ->setPublic(true);

        // PHTML Engine
        $builder->register(PHTMLEngine::class, PHTMLEngine::class)
            ->setPublic(true)
            ->setAutowired(true)
            ->addTag('template.engine');

        // Twig Engine
        $builder->register(FileLoader::class, FileLoader::class)
            ->setAutowired(true);
        $builder->register(Extension::class, Extension::class)
            ->setAutowired(true);
        $builder->register(TwigEngine::class, TwigEngine::class)
            ->setPublic('true')
            ->setAutowired(true)
            ->addTag('template.engine');

        $builder->registerAttributeForAutoconfiguration(
            AsTemplateEngine::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('template.engine');
            }
        );

        $builder->registerAttributeForAutoconfiguration(
            AsFunctionContainer::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('template.functions');
            }
        );

        $builder->addCompilerPass(new EngineCompilerPass());
        $builder->addCompilerPass(new FunctionCompilerPass());
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

    private function registerAdminAjaxContainer(ContainerBuilder $builder): void
    {
        $builder->register('adminajax.loader', AdminAjaxLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new AdminAjaxLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsAdminAjaxContainer::class,
            static function (
                ChildDefinition $defintion
            ): void {
                $defintion->addTag('adminajax.container');
            }
        );
    }

    private function registerAdminPages(ContainerBuilder $builder): void
    {
        $builder->addCompilerPass(new TabbedPageCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            IsTabbedPage::class,
            static function (
                ChildDefinition $defintion
            ): void {
                $defintion->addTag('adminpages.tabbed');
            }
        );

        $builder->register(TabbedPageTemplate::class, TabbedPageTemplate::class)
            ->setAutoconfigured(true)
            ->setAutowired(true);
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
                ChildDefinition $definition
            ): void {
                $definition->addTag('hook.container');
            }
        );
    }

    private function registerMetaboxContainer(ContainerBuilder $builder)
    {
        $builder->register('metabox.loader', MetaboxLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new MetaboxLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsMetaboxContainer::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('metabox.container');
            }
        );
    }

    private function registerBulkeditContainer(ContainerBuilder $builder)
    {
        $builder->register('bulkedit.loader', BulkeditLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new BulkeditLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsBulkEditContainer::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('bulkedit.container');
            }
        );
    }

    private function registerQuickeditContainer(ContainerBuilder $builder)
    {
        $builder->register('quickedit.loader', QuickeditLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new QuickeditLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsQuickEditContainer::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('quickedit.container');
            }
        );
    }

    private function registerCustomColumnContainer(ContainerBuilder $builder)
    {
        $builder->register('customColumn.loader', CustomColumnLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new CustomColumnLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsCustomColumnContainer::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('customColumn.container');
            }
        );
    }

    private function registerGutenbergBlock(ContainerBuilder $builder): void
    {
        $builder->register('gutenbergblock.loader', GutenbergBlockLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new GutenbergBlockCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsDynamicBlock::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('gutenbergblock.dynamicblock');
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

    private function registerFacadeContainer(ContainerBuilder $builder): void
    {
        $builder->register('facade.loader', FacadeLoader::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->addCompilerPass(new FacadeLoaderCompilerPass());

        $builder->registerAttributeForAutoconfiguration(
            AsFacade::class,
            static function (
                ChildDefinition $definition
            ): void {
                $definition->addTag('facade.container');
            }
        );
    }

    private function registerPathsContainer(ContainerBuilder $builder): void
    {
        $builder->register(Paths::class, Paths::class)
            ->setArgument('$basePath', $this->base->getPath())
            ->setArgument('$baseUrl', $this->base->getUrl())
            ->setPublic(true)
            ->setAutowired(false);
    }

}

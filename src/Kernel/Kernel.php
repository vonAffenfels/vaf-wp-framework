<?php

namespace VAF\WP\Framework\Kernel;

use Closure;
use Exception;
use ReflectionObject;
use RuntimeException;
use Symfony\Component\Config\Builder\ConfigBuilderGenerator;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The Kernel is the heart of the library system.
 */
abstract class Kernel
{
    private const CONTAINER_CLASS = 'CachedContainer';

    protected ?Container $container = null;

    protected bool $booted = false;

    public function __construct(
        protected readonly string $projectDir,
        protected readonly bool $debug,
        protected readonly string $namespace
    ) {
    }

    private function __clone()
    {
    }

    /**
     * @throws Exception
     */
    public function boot(): void
    {
        if (true === $this->booted) {
            return;
        }

        $this->bootHandler();

        $this->booted = true;
    }

    protected function bootHandler(): void
    {
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Gets the application root dir (path of the project's composer file).
     */
    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function getBuildDir(): string
    {
        return $this->getProjectDir() . '/container/';
    }

    public function getCharset(): string
    {
        return 'UTF-8';
    }

    abstract protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void;

    /**
     * @throws Exception
     */
    private function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {
            $kernelClass = str_contains(static::class, "@anonymous\0") ? self::class : static::class;

            if (!$container->hasDefinition('kernel')) {
                $container->register('kernel', $kernelClass)
                    ->setAutoconfigured(true)
                    ->setSynthetic(true)
                    ->setPublic(true);
            }

            $container->addObjectResource($this);

            $file = (new ReflectionObject($this))->getFileName();
            /* @var ContainerPhpFileLoader $kernelLoader */
            $kernelLoader = $loader->getResolver()->resolve($file);
            $kernelLoader->setCurrentDir(dirname($file));
            $instanceof = Closure::bind(function &() {
                return $this->instanceof;
            }, $kernelLoader, $kernelLoader)();

            $valuePreProcessor = AbstractConfigurator::$valuePreProcessor;
            AbstractConfigurator::$valuePreProcessor = function ($value) {
                return $this === $value ? new Reference('kernel') : $value;
            };

            try {
                $this->configureContainer(
                    new ContainerConfigurator(
                        $container,
                        $kernelLoader,
                        $instanceof,
                        $file,
                        $file
                    ),
                    $loader,
                    $container
                );
            } finally {
                $instanceof = [];
                $kernelLoader->registerAliasesForSinglyImplementedInterfaces();
                AbstractConfigurator::$valuePreProcessor = $valuePreProcessor;
            }

            // Register all parent classes of kernel as aliases
            foreach (class_parents($this) as $parent) {
                if (!$container->hasAlias($parent)) {
                    $container->setAlias($parent, 'kernel');
                }
            }

            $container->setAlias($kernelClass, 'kernel')->setPublic(true);
        });
    }

    public function getContainer(): ContainerInterface
    {
        if (!is_null($this->container)) {
            return $this->container;
        }

        $buildDir = $this->getBuildDir();
        $cache = new ConfigCache($buildDir . '/' . self::CONTAINER_CLASS . '.php', $this->debug);
        $cachePath = $cache->getPath();

        if (!$cache->isFresh()) {
            // Rebuild the whole container
            $container = $this->buildContainer();
            $container->compile();

            // cache the container
            $dumper = new PhpDumper($container);

            $code = $dumper->dump([
                'class' => self::CONTAINER_CLASS,
                'namespace' => $this->namespace
            ]);

            $cache->write($code, $container->getResources());
        }

        if (file_exists($cachePath)) {
            require_once $cachePath;

            $class = $this->namespace . "\\" . self::CONTAINER_CLASS;
            $this->container = new $class();
            $this->container->set('kernel', $this);
        }

        return $this->container;
    }

    /**
     * Returns the kernel parameters.
     */
    private function getKernelParameters(): array
    {
        return [
            'kernel.project_dir' => realpath($this->getProjectDir()) ?: $this->getProjectDir(),
            'kernel.runtime_environment' => '%env(default:kernel.environment:APP_RUNTIME_ENV)%',
            'kernel.debug' => $this->debug,
            'kernel.build_dir' => realpath($this->getBuildDir()) ?: $this->getBuildDir(),
            'kernel.charset' => $this->getCharset(),
            'kernel.container_class' => self::CONTAINER_CLASS,
        ];
    }

    /**
     * Builds the service container.
     *
     * @throws RuntimeException
     * @throws Exception
     */
    private function buildContainer(): ContainerBuilder
    {
        $dirs = ['build' => $this->getBuildDir()];
        foreach ($dirs as $name => $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                    throw new RuntimeException(sprintf('Unable to create the "%s" directory (%s).', $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new RuntimeException(sprintf('Unable to write in the "%s" directory (%s).', $name, $dir));
            }
        }

        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);
        $this->registerContainerConfiguration($this->getContainerLoader($container));

        return $container;
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     */
    private function getContainerBuilder(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->getParameterBag()->add($this->getKernelParameters());

        if ($this instanceof CompilerPassInterface) {
            $container->addCompilerPass($this, PassConfig::TYPE_BEFORE_OPTIMIZATION, -10000);
        }

        return $container;
    }

    /**
     * Returns a loader for the container.
     */
    private function getContainerLoader(ContainerBuilder $container): DelegatingLoader
    {
        $locator = new FileLocator();
        $resolver = new LoaderResolver([
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader(
                $container,
                $locator,
                null,
                class_exists(ConfigBuilderGenerator::class)
                    ? new ConfigBuilderGenerator($this->getBuildDir())
                    : null
            ),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new ClosureLoader($container),
        ]);

        return new DelegatingLoader($resolver);
    }
}

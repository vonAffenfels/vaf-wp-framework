<?php

namespace VAF\WP\Framework\Kernel;

use Closure;
use Exception;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
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
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader as ContainerPhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

/**
 * The Kernel is the heart of the library system.
 */
abstract class Kernel
{
    protected ?Container $container = null;

    protected bool $booted = false;

    public function __construct(protected readonly string $projectDir, protected readonly bool $debug)
    {
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

        if (null === $this->container) {
            $this->initializeContainer();
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

    public function getContainer(): ContainerInterface
    {
        if (!$this->container) {
            throw new LogicException('Cannot retrieve the container from a non-booted kernel.');
        }

        return $this->container;
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

    /**
     * Gets the container class.
     *
     * @throws InvalidArgumentException If the generated classname is invalid
     */
    private function getContainerClass(): string
    {
        $class = static::class;
        $class = str_contains($class, "@anonymous\0")
            ? get_parent_class($class) . str_replace('.', '_', ContainerBuilder::hash($class))
            : $class;
        return str_replace('\\', '_', $class)
            . ($this->debug ? 'Debug' : '') . 'Container';
    }

    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     */
    private function getContainerBaseClass(): string
    {
        return 'Container';
    }

    /**
     * Initializes the service container.
     *
     * The built version of the service container is used when fresh, otherwise the
     * container is built.
     * @throws Exception
     */
    private function initializeContainer(): void
    {
        $class = $this->getContainerClass();
        $buildDir = $this->getBuildDir();
        $cache = new ConfigCache($buildDir . '/' . $class . '.php', $this->debug);
        $cachePath = $cache->getPath();

        // Silence E_WARNING to ignore "include" failures - don't use "@" to prevent silencing fatal errors
        $errorLevel = error_reporting(E_ALL ^ E_WARNING);

        try {
            if (
                is_file($cachePath)
                && is_object($this->container = include $cachePath)
                && (!$this->debug || $cache->isFresh())
            ) {
                $this->container->set('kernel', $this);
                error_reporting($errorLevel);

                return;
            }
        } catch (Throwable $e) {
        }

        $oldContainer = is_object($this->container) ? new ReflectionClass($this->container) : $this->container = null;

        try {
            is_dir($buildDir) || mkdir($buildDir, 0777, true);

            if ($lock = fopen($cachePath . '.lock', 'w')) {
                if (
                    !flock($lock, LOCK_EX | LOCK_NB, $wouldBlock)
                    && !flock($lock, $wouldBlock ? LOCK_SH : LOCK_EX)
                ) {
                    fclose($lock);
                    $lock = null;
                } elseif (!is_file($cachePath) || !is_object($this->container = include $cachePath)) {
                    $this->container = null;
                } elseif (!$oldContainer || get_class($this->container) !== $oldContainer->name) {
                    flock($lock, LOCK_UN);
                    fclose($lock);
                    $this->container->set('kernel', $this);

                    return;
                }
            }
        } catch (Throwable $e) {
        } finally {
            error_reporting($errorLevel);
        }

        $container = null;
        $container = $this->buildContainer();
        $container->compile();

        $this->dumpContainer($cache, $container, $class, $this->getContainerBaseClass());

        if ($lock) {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        $this->container = require $cachePath;
        $this->container->set('kernel', $this);

        if ($oldContainer && get_class($this->container) !== $oldContainer->name) {
            // Because concurrent requests might still be using them,
            // old container files are not removed immediately,
            // but on a next dump of the container.
            static $legacyContainers = [];
            $oldContainerDir = dirname($oldContainer->getFileName());
            $legacyContainers[$oldContainerDir . '.legacy'] = true;
            $glob = glob(dirname($oldContainerDir) . DIRECTORY_SEPARATOR . '*.legacy', GLOB_NOSORT);
            foreach ($glob as $legacyContainer) {
                if (!isset($legacyContainers[$legacyContainer]) && @unlink($legacyContainer)) {
                    (new Filesystem())->remove(substr($legacyContainer, 0, -7));
                }
            }

            touch($oldContainerDir . '.legacy');
        }
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
            'kernel.container_class' => $this->getContainerClass(),
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
     * Dumps the service container to PHP code in the cache.
     *
     * @param string $class     The name of the class to generate
     * @param string $baseClass The name of the container's base class
     */
    private function dumpContainer(
        ConfigCache $cache,
        ContainerBuilder $container,
        string $class,
        string $baseClass
    ): void {
        // cache the container
        $dumper = new PhpDumper($container);

        $content = $dumper->dump([
            'class' => $class,
            'base_class' => $baseClass,
            'file' => $cache->getPath(),
            'as_files' => true,
            'debug' => $this->debug,
            'build_time' => $container->hasParameter('kernel.container_build_time')
                ? $container->getParameter('kernel.container_build_time')
                : time(),
            'preload_classes' => [],
        ]);

        $rootCode = array_pop($content);
        $dir = dirname($cache->getPath()) . '/';
        $fs = new Filesystem();

        foreach ($content as $file => $code) {
            $fs->dumpFile($dir . $file, $code);
            @chmod($dir . $file, 0666 & ~umask());
        }
        $legacyFile = dirname($dir . key($content)) . '.legacy';
        if (is_file($legacyFile)) {
            @unlink($legacyFile);
        }

        $cache->write($rootCode, $container->getResources());
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

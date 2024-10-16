<?php

namespace VAF\WP\Framework;

use Exception;
use Isolated\Symfony\Component\Finder\Finder;

final class PHPScoperConfigGenerator
{
    private array $ignoredPackages = [
        'friendsofphp/php-cs-fixer',
        'humbug/php-scoper',
        'squizlabs/php_codesniffer',
        'roave/security-advisories'
    ];

    private array $ignoredNamespaces = [];

    private array $packagesProcessed = [];

    private array $packagesToProcess = [];

    private array $patchers = [];

    public function __construct(
        private readonly string $baseDir,
        private readonly string $prefix,
        private readonly string $buildDir
    ) {
        $this->ignorePackage('phpunit/phpunit');
        $this->ignorePackage('pestphp/pest');
        $this->ignorePackage('mockery/mockery');
        $this->ignorePackage('twig/twig');
        $this->ignoreNamespace('/^Twig/');

        $this->addPackagePatcher(
            'symfony/dependency-injection',
            function (string $filePath, string $prefix, string $content): string {
                return $this->patchSymfonyDI($filePath, $prefix, $content);
            }
        );

        $this->addPackagePatcher(
            'vonaffenfels/vaf-wp-framework',
            function (string $filePath, string $prefix, string $content): string {
                return $this->patchVAFFramework($filePath, $prefix, $content);
            }
        );

        $this->addPackagePatcher(
            'vonaffenfels/vaf-wp-framework',
            function (string $filePath, string $prefix, string $content): string {
                return str_replace(
                    sprintf("%s\\WP_REST_Request", $prefix),
                    "WP_REST_Request",
                    $content
                );
            }
        );

    }

    public function ignorePackage(string $package): void
    {
        if (!in_array($package, $this->ignoredPackages)) {
            $this->ignoredPackages[] = $package;
        }
    }

    private function ignoreNamespace(string $namespace): void
    {
        if (!in_array($namespace, $this->ignoredNamespaces)) {
            $this->ignoredNamespaces[] = $namespace;
        }
    }

    public function addPackagePatcher(string $package, callable $patcher): void
    {
        $this->patchers[$package] = $this->patchers[$package] ?? [];
        $this->patchers[$package][] = function () use ($patcher) {
            return call_user_func_array($patcher, func_get_args());
        };
    }

    private function getRequiredPackages(string $pathToComposerJson, bool $useRequireDev = false): array
    {
        if (!file_exists($pathToComposerJson) && !is_readable($pathToComposerJson)) {
            return [];
        }

        $composerData = json_decode(file_get_contents($pathToComposerJson), true);
        $packages = [];

        if ($useRequireDev && is_array($composerData['require-dev'] ?? null)) {
            $packages = array_keys($composerData['require-dev']);
        } elseif (is_array($composerData['require'] ?? null)) {
            $packages = array_keys($composerData['require']);
        }

        return array_filter($packages, function (string $package): bool {
            if (!str_contains($package, '/')) {
                // Composer packages have to include at least one /
                return false;
            }

            // Filter ignored packages
            // and already processed packages
            // and packages already marked for processing
            return
                !in_array($package, $this->ignoredPackages) &&
                !in_array($package, $this->packagesProcessed) &&
                !in_array($package, $this->packagesToProcess);
        });
    }

    private function buildFinderForPackage(string $package): ?Finder
    {
        $path = $this->baseDir . '/vendor/' . $package;
        if (false === realpath($path)) {
            return null;
        }
        return Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->in(['vendor/' . $package]);
    }

    /**
     * @throws Exception
     */
    public function buildConfig(): array
    {
        $rootComposerJson = realpath($this->baseDir . '/composer.json');
        if (false === $rootComposerJson) {
            throw new Exception(
                sprintf(
                    'Could not find root composer.json in path %s!',
                    $this->baseDir . '/composer.json'
                )
            );
        }

        $this->packagesToProcess = $this->getRequiredPackages(
            $rootComposerJson,
            true
        );

        $finders = [];
        while (!empty($this->packagesToProcess)) {
            $package = array_shift($this->packagesToProcess);
            $this->packagesProcessed[] = $package;

            $finder = $this->buildFinderForPackage($package);
            if (!is_null($finder)) {
                $finders[] = $finder;
            }

            $packageComposerJson = realpath($this->baseDir . '/vendor/' . $package . '/composer.json');
            if (false !== $packageComposerJson) {
                $newPackages = $this->getRequiredPackages($packageComposerJson);

                $this->packagesToProcess = array_merge(
                    $this->packagesToProcess,
                    $newPackages
                );
            }
        }

        $patchers = [];
        foreach ($this->packagesProcessed as $package) {
            if (isset($this->patchers[$package])) {
                $patchers = array_merge($patchers, $this->patchers[$package]);
            }
        }

        return [
            'prefix' => $this->prefix,
            'output-dir' => $this->buildDir,
            'finders' => $finders,
            'exclude-namespaces' => $this->ignoredNamespaces,
            'patchers' => $patchers
        ];
    }

    private function patchVAFFramework(string $filePath, string $prefix, string $content): string
    {
        if (!str_contains($filePath, 'templates/admin/notice.phtml')) {
            return $content;
        }

        return str_replace(
            'VAF\WP\Framework\Utils\NoticeType::INFO',
            $prefix . '\VAF\WP\Framework\Utils\NoticeType::INFO',
            $content
        );
    }

    private function patchSymfonyDI(string $filePath, string $prefix, string $content): string
    {
        if (!str_contains($filePath, 'Compiler/ResolveInstanceofConditionalsPass.php')) {
            return $content;
        }

        $lenPrefix = strlen($prefix . '\\');

        $content = preg_replace_callback(
            '/\$definition = \\\\?substr_replace\(\$definition, \'([0-9]+)\', 2, 2\);/',
            function (array $matches) use ($lenPrefix): string {
                $origLength = $matches[1];
                $newLength = $origLength + $lenPrefix;
                return str_replace("'{$origLength}'", "'{$newLength}'", $matches[0]);
            },
            $content
        );

        return preg_replace_callback(
            '/\$definition = \\\\?substr_replace\(\$definition, \'Child\', ([0-9]+), 0\);/',
            function (array $matches) use ($lenPrefix): string {
                $origOffset = $matches[1];
                $newOffset = $origOffset + $lenPrefix;

                return str_replace(", {$origOffset}, ", ", {$newOffset}, ", $matches[0]);
            },
            $content
        );
    }

}

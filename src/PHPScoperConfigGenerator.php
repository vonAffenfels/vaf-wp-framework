<?php

namespace VAF\WP\Framework;

use Isolated\Symfony\Component\Finder\Finder;

final class PHPScoperConfigGenerator
{
    private array $ignoredPackages = [
        'friendsofphp/php-cs-fixer',
        'humbug/php-scoper',
        'squizlabs/php_codesniffer',
        'roave/security-advisories'
    ];

    private array $packagesProcessed = [];

    private array $packagesToProcess = [];

    public function __construct(
        private readonly string $baseDir,
        private readonly string $prefix,
        private readonly string $buildDir
    ) {
    }

    public function ignorePackage(string $package): void
    {
        if (!in_array($package, $this->ignoredPackages)) {
            $this->ignoredPackages[] = $package;
        }
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

    public function buildConfig(): array
    {
        $rootComposerJson = realpath($this->baseDir . '/composer.json');
        if (false === $rootComposerJson) {
            throw new \Exception(
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

        return [
            'prefix' => $this->prefix,
            'output-dir' => $this->buildDir,
            'finders' => $finders,
            'patchers' => []
        ];
    }

    private static function getPatcherSymfonyDI(): callable
    {
        return static function (string $filePath, string $prefix, string $content): string {
            if (!str_contains($filePath, 'Compiler/ResolveInstanceofConditionalsPass.php')) {
                return $content;
            }

            $lenPrefix = strlen($prefix . '\\');

            $content = preg_replace_callback(
                '/\$definition = \\\\substr_replace\(\$definition, \'(53)\', 2, 2\);/',
                function (array $matches) use ($lenPrefix): string {
                    $origLength = $matches[1];
                    $newLength = $origLength + $lenPrefix;
                    return str_replace("'{$origLength}'", "'{$newLength}'", $matches[0]);
                },
                $content
            );

            return preg_replace_callback(
                '/\$definition = \\\\substr_replace\(\$definition, \'Child\', (44), 0\);/',
                function (array $matches) use ($lenPrefix): string {
                    $origOffset = $matches[1];
                    $newOffset = $origOffset + $lenPrefix;

                    return str_replace(", {$origOffset}, ", ", {$newOffset}, ", $matches[0]);
                },
                $content
            );
        };
    }
}

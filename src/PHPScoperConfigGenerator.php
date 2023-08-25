<?php

namespace VAF\WP\Framework;

use Isolated\Symfony\Component\Finder\Finder;

final class PHPScoperConfigGenerator
{
    private static function getFinderPsr(): Finder
    {
        return Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->in(['vendor/psr/container']);
    }

    private static function getFinderSymfony(): Finder
    {
        return Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->in([
                'vendor/symfony/config',
                'vendor/symfony/dependency-injection',
                'vendor/symfony/deprecation-contracts',
                'vendor/symfony/filesystem',
                'vendor/symfony/polyfill-ctype',
                'vendor/symfony/polyfill-mbstring',
                'vendor/symfony/service-contracts',
                'vendor/symfony/var-exporter',
                'vendor/symfony/yaml'
            ]);
    }

    private static function getPatcherSymfonyDI(): callable
    {
        return static function (string $filePath, string $prefix, string $content): string
        {
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

    private static function getFinderTwig(): Finder
    {
        return Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->in([
                'vendor/twig/twig'
            ]);
    }

    public static function generateConfig(
        string $prefix,
        string $outputDir = './vendor_prefixed',
        array $finders = [],
        array $patchers = []
    ): array {
        $finders = array_merge([
            self::getFinderPsr(),
            self::getFinderSymfony(),
            self::getFinderTwig()
        ], $finders);

        $patchers = array_merge([
            self::getPatcherSymfonyDI()
        ], $patchers);

        return [
            'prefix' => $prefix,
            'output-dir' => $outputDir,
            'finders' => $finders,
            'patchers' => $patchers
        ];
    }
}
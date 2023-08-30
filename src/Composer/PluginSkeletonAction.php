<?php

namespace VAF\WP\Framework\Composer;

use Composer\Factory;
use Composer\Json\JsonFile;
use Composer\Pcre\Preg;
use Composer\Script\Event;
use Exception;
use InvalidArgumentException;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class PluginSkeletonAction
{
    private static ?array $gitConfig = null;

    private static function getGitConfig(): array
    {
        if (null !== self::$gitConfig) {
            return self::$gitConfig;
        }

        $finder = new ExecutableFinder();
        $gitBin = $finder->find('git');

        $cmd = new Process([$gitBin, 'config', '-l']);
        $cmd->run();

        if ($cmd->isSuccessful()) {
            self::$gitConfig = [];
            Preg::matchAllStrictGroups('{^([^=]+)=(.*)$}m', $cmd->getOutput(), $matches);
            foreach ($matches[1] as $key => $match) {
                self::$gitConfig[$match] = $matches[2][$key];
            }

            return self::$gitConfig;
        }

        return self::$gitConfig = [];
    }

    private static function isValidEmail(string $email): bool
    {
        // assume it's valid if we can't validate it
        if (!function_exists('filter_var')) {
            return true;
        }

        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @throws ParsingException
     * @throws Exception
     */
    private static function patchComposerJson(
        string $slug,
        string $pluginDescription,
        string $namespace,
        string $authorName,
        string $authorEmail,
        string $website
    ): void {
        $file = Factory::getComposerFile();
        $composerJson = new JsonFile($file);
        $composerData = $composerJson->read();

        // Remove post-create-project-cmd script
        unset($composerData['scripts']['post-create-project-cmd']);

        // Add build container command
        $composerData['scripts']['build-container'] = [
            $namespace . '\Plugin::buildContainer'
        ];

        $authorData = [];
        if (!empty($authorName)) {
            $authorData['name'] = $authorName;
        }
        if (!empty($authorEmail)) {
            $authorData['email'] = $authorEmail;
        }
        if (!empty($website)) {
            $authorData['homepage'] = $website;
        }

        $composerData['name'] = 'vonaffenfels/' . $slug;
        $composerData['type'] = 'wordpress-plugin';

        if (!empty($pluginDescription)) {
            $composerData['description'] = $pluginDescription;
        } else {
            unset($composerData['description']);
        }

        if (!empty($website)) {
            $composerData['homepage'] = $website;
        } else {
            unset($composerData['homepage']);
        }

        if (!empty($authorData)) {
            $composerData['authors'] = [
                $authorData
            ];
        } else {
            unset($composerData['authors']);
        }

        $composerJson->write($composerData);
    }

    private static function createConfigServicesYaml(string $namespace): void
    {
        $content = <<<END
services:
  _defaults:
    autowire: true
    autoconfigure: true

  $namespace\:
    resource: '../src/'
    exclude:
      - '../src/Plugin.php'
END;
        file_put_contents(realpath('./config/services.yaml'), $content);
    }

    private static function createSrcPluginPhp(string $namespace, string $vendorNamespace): void
    {
        $content = <<<END
<?php

namespace $namespace;

class Plugin extends \\$vendorNamespace\\VAF\\WP\\Framework\\Plugin
{
}

END;

        file_put_contents(realpath('./src/Plugin.php'), $content);
    }

    private static function createScoperIncPhp(string $vendorNamespace): void
    {
        $content = <<<END
<?php

use VAF\\WP\\Framework\\PHPScoperConfigGenerator;

\$scoperConfigGen = new PHPScoperConfigGenerator(
    baseDir: __DIR__,
    prefix: '$vendorNamespace',
    buildDir: './vendor_prefixed'
);

// Do not prefix ignored packages
//\$scoperConfigGen->ignorePackage('myvendor/mypackage');

return \$scoperConfigGen->buildConfig();

END;

        file_put_contents(realpath('./scoper.inc.php'), $content);
    }

    private static function createMainPluginFile(
        string $slug,
        string $pluginName,
        string $pluginDescription,
        string $namespace,
        string $authorName,
        string $authorEmail,
        string $website
    ): void {
        $pluginName = " * Plugin Name:       $pluginName";
        if (!empty($pluginDescription)) {
            $pluginDescription = " * Description:       $pluginDescription\n";
        }

        $authorComplete = '';
        if (!empty($authorName) && !empty($authorEmail)) {
            $authorComplete = sprintf('%s <%s>', $authorName, $authorEmail);
        } elseif (!empty($authorName)) {
            $authorComplete = $authorName;
        } elseif (!empty($authorEmail)) {
            $authorComplete = $authorEmail;
        }
        if (!empty($authorComplete)) {
            $authorComplete = " * Author:            $authorComplete\n";
        }

        if (!empty($website)) {
            $website = " * Author URI:        $website\n";
        }

        $versionString = " * Version:           1.0.0\n";
        $requiresString = " * Requires at least: 6.2\n";

        $content = <<<END
<?php

/**
$pluginName$pluginDescription$authorComplete$website$versionString$requiresString
 */

use $namespace\\Plugin;

if (!defined('ABSPATH')) {
    die('');
}

\$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists(\$autoloadPath)) {
    require_once \$autoloadPath;
}

Plugin::registerPlugin(__FILE__, defined('WP_DEBUG') && WP_DEBUG);

END;

        file_put_contents("./$slug.php", $content);

        // Delete skeleton file
        unlink(realpath('./wp-plugin-skeleton.php'));
    }

    /**
     * @throws Exception
     */
    public static function onPostCreateProject(Event $event): void
    {
        $io = $event->getIO();

        $io->writeError([
            '',
            'This command will set-up your new plugin using the vonAffenfels Wordpress Framework.',
            '',
        ]);

        $git = self::getGitConfig();
        $cwd = realpath('.');

        $pluginName = $io->askAndValidate(
            'Plugin name: ',
            function (?string $value): string {
                if (empty($value)) {
                    throw new InvalidArgumentException(
                        'You must provide a name for your plugin!'
                    );
                }

                return $value;
            }
        );

        $pluginDescription = $io->ask('Plugin description []: ', '');

        $slug = basename($cwd);
        $slug = $io->ask('Plugin slug [<comment>' . $slug . '</comment>]: ', $slug);

        $namespace = str_replace(' ', '\\', ucwords(str_replace('-', ' ', $slug)));
        $namespace = $io->ask('Plugin namespace [<comment>' . $namespace . '</comment>]: ', $namespace);

        $vendorNamespace = str_replace('\\', '', $namespace) . '_Vendor';
        $vendorNamespace = $io->ask(
            'Vendor namespace [<comment>' . $vendorNamespace . '</comment>]: ',
            $vendorNamespace
        );

        $authorName = $_SERVER['COMPOSER_DEFAULT_AUTHOR'] ?? $git['user.name'] ?: '';
        $authorName = $io->ask('Author [<comment>' . $authorName . '</comment>]: ', $authorName);

        $authorEmail = $_SERVER['COMPOSER_DEFAULT_EMAIL'] ?? $git['user.email'] ?: '';
        $authorEmail = $io->askAndValidate(
            'Author e-mail [<comment>' . $authorEmail . '</comment>]: ',
            function (?string $value) use ($authorEmail): string {
                if (empty($value)) {
                    return $authorEmail;
                }

                if (!self::isValidEmail($value)) {
                    throw new InvalidArgumentException('Invalid email "' . $value . '"');
                }

                return $value;
            }
        );

        $website = $io->ask('Website []: ', '');

        $io->writeError([
            '',
            'Using the following informations:',
            '<info>Plugin name: </info>' . $pluginName,
            '<info>Plugin description: </info>' . $pluginDescription,
            '<info>Plugin slug: </info>' . $slug,
            '<info>Namespace: </info>' . $namespace,
            '<info>Vendor Namespace: </info>' . $vendorNamespace,
            '<info>Author: </info>' . $authorName,
            '<info>Author e-mail: </info>' . $authorEmail,
            '<info>Website: </info>' . $website
        ]);

        if (!$io->askConfirmation('Are those information correct? [<comment>no</comment>] ', false)) {
            $io->writeError('Cancelling.');
            $io->writeError('To restart simply run "composer run-script post-create-project-cmd"');
            return;
        }

        self::createConfigServicesYaml($namespace);
        self::createSrcPluginPhp($namespace, $vendorNamespace);
        self::createScoperIncPhp($vendorNamespace);
        self::createMainPluginFile(
            $slug,
            $pluginName,
            $pluginDescription,
            $namespace,
            $authorName,
            $authorEmail,
            $website
        );

        self::patchComposerJson(
            $slug,
            $pluginDescription,
            $namespace,
            $authorName,
            $authorEmail,
            $website
        );

        $io->writeError([
            '',
            '<info>Finished</info>',
            'Don\'t forget to check files and do last changes',
            'Happy development'
        ]);
    }
}

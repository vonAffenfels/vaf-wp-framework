<?php

namespace VAF\WP\Framework\Composer;

use Composer\Factory;
use Composer\Json\JsonFile;
use Composer\Pcre\Preg;
use Composer\Script\Event;
use Exception;
use InvalidArgumentException;
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

    private static function patchFile(string $file, array $replacements): bool
    {
        $content = file_get_contents($file);
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        file_put_contents($file, $content);
        return true;
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

        $authorComplete = '';
        if (!empty($authorName) && !empty($authorEmail)) {
            $authorComplete = sprintf('%s <%s>', $authorName, $authorEmail);
        } elseif (!empty($authorName)) {
            $authorComplete = $authorName;
        } elseif (!empty($authorEmail)) {
            $authorComplete = $authorEmail;
        }

        $replacementArray = [
            '%%PLUGIN_NAME%%' => $pluginName,
            '%%PLUGIN_DESCRIPTION%%' => $pluginDescription,
            '%%PLUGIN_SLUG%%' => $slug,
            '%%PLUGIN_NAMESPACE%%' => $namespace,
            '%%VENDOR_NAMESPACE%%' => $vendorNamespace,
            '%%WEBSITE%%' => $website,
            '%%AUTHOR_COMPLETE%%' => $authorComplete,
            '%%AUTHOR_NAME%%' => $authorName,
            '%%AUTHOR_EMAIL%%' => $authorEmail,
        ];

        $filesToPatch = [
            'config/services.yaml',
            'src/Plugin.php',
            'wp-plugin-skeleton.php',
            'scoper.inc.php',
        ];

        foreach ($filesToPatch as $file) {
            if (!self::patchFile(realpath($cwd . '/' . $file), $replacementArray)) {
                $io->writeError('<error>Error patching file "' . $file . '"!</error>');
                return;
            }
        }

        $file = Factory::getComposerFile();
        $composerJson = new JsonFile($file);
        $composerData = $composerJson->read();

        // Remove post-create-project-cmd script
        if (isset($composerData['scripts']['post-create-project-cmd'])) {
            unset($composerData['scripts']['post-create-project-cmd']);
        }

        // Add build container command
        $composerData['scripts']['build-container'] = [
            $namespace . '\Plugin::buildContainer'
        ];

        // Add post-dump-autoload command
        $composerData['scripts']['post-autoload-dump'] = [
            'VAF\\WP\\Framework\\Composer\\PluginActions::prefixDependencies'
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

        $io->writeError([
            '<info>Finished</info>',
            'Don\'t forget to check files and do last changes',
            'Happy development'
        ]);
    }
}

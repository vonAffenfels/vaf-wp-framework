<?php

namespace VAF\WP\Framework\Composer;

use Composer\Script\Event;

class PluginActions
{
    public static function postCreateProject(Event $event): void
    {
        $io = $event->getIO();

        $pluginName = $io->ask('Plugin name: ');
        var_dump($pluginName);
    }

    public static function prefixDependencies(Event $event): void
    {
        $io = $event->getIO();

        if (!$event->isDevMode()) {
            $io->write('Not prefixing dependencies, due to not being in dev mode.');
            return;
        }

        if (!file_exists(getcwd() . '/vendor/bin/php-scoper')) {
            $io->write('Not prefixing dependencies, due to PHP scoper not being installed');
        }

        $io->write('Prefixing dependencies...');

        var_dump($event->getComposer()->getPackage()->getExtra());

        $eventDispatcher = $event->getComposer()->getEventDispatcher();
        $eventDispatcher->addListener(
            'internal-prefix-dependencies',
            '@php vendor/humbug/php-scoper/bin/php-scoper add-prefix --config=scoper.inc.php --force'
        );
        $eventDispatcher->addListener(
            'internal-prefix-dependencies',
            '@composer du --no-scripts'
        );
        $eventDispatcher->dispatch('internal-prefix-dependencies');
    }
}

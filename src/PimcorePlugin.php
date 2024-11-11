<?php

namespace Forge\Plugins\Pimcore;

use Forge\Composer\Composer;
use Forge\Plugins\Pimcore\Commands\CreateProjectCommand;
use Forge\Plugins\Pimcore\Commands\InitializeVueJsCommand;
use Forge\Plugins\Pimcore\Commands\MakeAdminMenu;
use Forge\Plugins\Pimcore\Commands\MakeAreabrickCommand;
use Forge\Plugins\Pimcore\Commands\MakeControllerCommand;
use Forge\Plugins\Pimcore\Commands\UpdateAreabricksCommand;
use Forge\Plugins\Plugin;
use Forge\Plugins\PluginServiceProviderInterface as ServiceProvider;

class PimcorePlugin extends Plugin
{
    public function __construct(protected Composer $composer)
    {
    }

    public static function name(): string
    {
        return 'PimcorePlugin';
    }

    /**
     * @return array<int, string>
     */
    public function commands(): array
    {
        $commands = [
            CreateProjectCommand::class,
        ];

        if ($this->composer->has('pimcore/pimcore')) {
            $commands[] = MakeControllerCommand::class;
            $commands[] = MakeAreabrickCommand::class;
            $commands[] = MakeAdminMenu::class;
            $commands[] = InitializeVueJsCommand::class;
            $commands[] = UpdateAreabricksCommand::class;
        }

        return $commands;
    }

    public function boot(ServiceProvider $provider): void
    {
        $commands = $this->commands();
        if (count($commands) > 0) {
            $provider->commands($commands);
        }

        $provider->loadStubsFrom(__DIR__.'/../stubs', $this->name());
    }
}

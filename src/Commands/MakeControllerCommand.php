<?php

namespace Forge\Plugins\Pimcore\Commands;

use Forge\Composer\Composer;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;


class MakeControllerCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'pimcore:make:controller {controller} {--a|admin} {--t|template} ';
    protected $description = 'Make a pimcore controller.';

    public function handle(Composer $composer): int
    {
        $isAdmin = $this->option('admin');
        $controllerName = $this->argument('controller');
        $view = $this->option('template');

        $this->createController($controllerName, $isAdmin);

        if ($view) {
            $this->createView($controllerName);
        }
        return self::SUCCESS;
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'controller' => 'What should we name the controller?',
            'admin' => 'Is this an admin controller? (yes/no)',
            'view' => 'Do you want to create a view? (yes/no)'
        ];
    }

    protected function createController(string $controller, bool $isAdmin): void
    {
        $controllerPath = sprintf('%s/src/Controller/%s', getcwd(), $isAdmin ? 'Admin/' : '');
        $controllerNamespace = sprintf('App\\Controller%s', $isAdmin ? '\\Admin' : '');
        $viewPath = sprintf('pages/%s/index.html.twig', $this->getViewName($controller));

        File::ensureDirectoryExists($controllerPath);

        if (File::exists(sprintf('%s/%s.php', $controllerPath, $controller))) {
            $overwrite = $this->anticipate(
                sprintf(
                    'Controller <fg=yellow>%s</> already exists at <fg=yellow>%s%s.php</> ' . PHP_EOL .
                        ' Do you want to overwrite?',
                    $controller,
                    $controllerPath,
                    $controller
                ),
                ['yes', 'no'],
                'no'
            );
            if ($overwrite === 'no') {
                return;
            }
        }

        File::put(
            path: sprintf('%s/%s.php', $controllerPath, $controller),
            contents: View::make('PimcorePlugin::pimcore-controller', [
                'class' => $controller,
                'namespace' => $controllerNamespace,
                'action' => 'index',
                'view' => $viewPath
            ])->render()
        );
        $this->info('Controller created at %s', $controllerPath);
    }

    protected function getViewName(string $controller): string
    {
        return str_replace('controller', '', strtolower($controller));
    }

    protected function createView(string $controller): void
    {
        $viewPath = sprintf('%s/templates/pages/%s/', getcwd(), $this->getViewName($controller));
        $viewName = 'index.html.twig';

        File::ensureDirectoryExists($viewPath);

        File::put(
            path: sprintf('%s/%s', $viewPath, $viewName),
            contents: View::make('PimcorePlugin::pimcore-page', [])->render()
        );
        $this->info('View created at %s', $viewPath);
    }
}

<?php

namespace Forge\Plugins\Pimcore\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use function Laravel\Prompts\form;

class MakeAdminMenu extends Command
{
    protected $signature = 'pimcore:make:admin-menu';
    protected $description = 'Make a Pimcore Admin Menu Item';

    public function handle(): int
    {
        $results = form()
            ->text(
                label: 'Enter an identifier for the admin menu:',
                hint: 'Identifier will be used for the file name, the module name and the translation label for menu items',
                validate: function ($value) {
                    if (empty($value)) {
                        return 'Identifier cannot be empty';
                    }
                    if (!preg_match('/^[a-z0-9_]+$/', $value)) {
                        return 'Identifier must be all lowercase and can contain only letters, numbers and underscores';
                    }
                },
                name: 'identifier'
            )
            ->select(
                label: 'Choose a flavor',
                hint: 'Flavor determines if the menu is compatible for newer pimcore admin ui versions or not',
                options: [
                    'legacy' => 'Legacy (pimcore 10.5 or older)',
                    'event' => 'Event Driven (pimcore 10.6 or newer)'
                ],
                validate: function ($value) {
                    if (empty($value)) {
                        return 'Flavor cannot be empty';
                    }

                    if (!in_array($value, ['legacy', 'event'])) {
                        return 'Invalid flavor. Choose one of the available options';
                    }
                },
                name: 'flavor'
            )
            ->submit();

        $template = $results['flavor'];

        $content = View::make('PimcorePlugin::menu.' . $template, [
            'name' => $results['identifier'],
        ])->render();

        $targetDirectory = sprintf('%s/public/static/js/pimcore', getcwd());
        File::ensureDirectoryExists($targetDirectory);

        $targetFile = sprintf('%s/%s.js', $targetDirectory, $results['identifier']);

        File::put($targetFile, $content);

        $this->info(sprintf('Admin menu created at %s', $targetFile));

        return self::SUCCESS;
    }
}

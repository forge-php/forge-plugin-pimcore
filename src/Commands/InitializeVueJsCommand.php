<?php

namespace Forge\Plugins\Pimcore\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\form;


class InitializeVueJsCommand extends Command
{
    protected $signature = 'pimcore:vue:init';
    protected $description = 'Initialize VueJs in pimcore project';

    public function handle(): int
    {
        $form = form()
            ->select(
                label: 'Choose a state management library',
                name: 'state_management',
                options: [
                    'pinia' => 'Pinia',
                    'none' => 'None'
                ]
            )
            ->submit();

        $composer = File::get(getcwd() . '/composer.json');
        $composer = json_decode($composer, true);
        $dependencies = $composer['require'] ?? [];
        $dependencies['pentatrion/vite-bundle'] = "*";

        $composer['require'] = $dependencies;
        File::put(getcwd() . '/composer.json', json_encode($composer, JSON_PRETTY_PRINT));

        $copy = [
            'tsconfig.app.json' => 'tsconfig.app.json',
            'tsconfig.node.json' => 'tsconfig.node.json',
            'tsconfig.json' => 'tsconfig.json',
            'vite.config.ts' => 'vite.config.ts',
        ];

        $packages = [];
        if ($form['state_management'] === 'pinia') {
            $packages['pinia'] = '*';
        }

        if ($form['state_management'] === 'vuex') {
            $packages['vuex'] = '*';
        }

        foreach ($copy as $source => $destination) {
            $file = __DIR__ . '/../../stubs/vuejs/' . $source;
            $destination = getcwd() . '/' . $destination;

            if ($this->output->isVerbose()) {
                $this->info(sprintf('Creating %s', $destination));
            }

            File::copy($file, $destination);
        }


        $packageJson = view('PimcorePlugin::vuejs.package_json', [
            'name' => 'pimcore-frontend',
            'dependencies' => $packages
        ])->render();

        File::put(getcwd() . '/package.json', $packageJson);

        return self::SUCCESS;
    }
}

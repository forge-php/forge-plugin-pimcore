<?php

namespace Forge\Plugins\Pimcore\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;


class CreateProjectCommand extends Command
{
    protected $signature = 'pimcore:create-project {name}';
    protected $description = 'Create a new pimcore project';

    public function handle(): int
    {
        $name = $this->argument('name');

        $path = sprintf('%s/%s', getcwd(), $name);

        if (File::isDirectory($path)) {
            $overwrite = \Laravel\Prompts\confirm('Directory already exists. Do you want to overwrite it?', false);

            if (!$overwrite) {
                return self::FAILURE;
            }

            File::deleteDirectories($path);
        }

        File::ensureDirectoryExists($path);

        $repository = \Laravel\Prompts\text('Enter the repository url: ');
        $process = new Process(['git', 'clone', '--depth=1', $repository, sprintf('%s/%s', $path, 'project')]);
        $process->setTty(true);
        $process->mustRun(function($type, $buffer) {
            $this->output->write($buffer);
        });

        return self::SUCCESS;
    }
}

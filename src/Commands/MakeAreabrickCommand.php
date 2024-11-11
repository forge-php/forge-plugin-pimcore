<?php

namespace Forge\Plugins\Pimcore\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeAreabrickCommand extends Command
{
    protected $signature = 'pimcore:make:areabrick';
    protected $description = 'Make a Pimcore Areabrick';


    public function handle(): int
    {
        $name = text(
            label: 'Enter the name of the areabrick',
            hint: 'The name should be in pascal case e.g. MyAreaBrick',
            validate: function($name) {
                if (!preg_match('/^[A-X][a-z]*$/', $name)) {
                    return 'The name should be in pascal case and should not contain any special characters, numbers or spaces. e.g. MyAreaBrick';
                }
            }
        );

        $this->createAreabrick($name);

        return self::SUCCESS;
    }

    protected function createAreabrick(string $areabrick): void
    {
        $areabrickPath = sprintf('%s/src/Document/Areabrick/', getcwd());
        $areabrickNamespace = sprintf('App\\Document\\Areabrick');
        $viewPath = sprintf('areas/%s/view.html.twig', $this->getViewName($areabrick));

        File::ensureDirectoryExists($areabrickPath);

        if (File::exists(sprintf('%s/%s.php', $areabrickPath, $areabrick))) {
            $overwrite = confirm(
                label: sprintf(
                    'Areabrick %s already exists! Do you want to overwrite?',
                    $areabrick,
                ),
                default: false,
                yes: 'Overwrite',
                no: 'Cancel'
            );
            if (!$overwrite) {
                return;
            }
        }

        $extendsCustom = confirm(
            label: 'Do you want to extend a custom areabrick?',
            default: false,
            yes: 'Yes',
            no: 'No',
            hint: 'Select yes if you have a base areabrick implementation in your src/Document/Areabrick directory that other areabricks extend'
        );

        $extends = 'Pimcore\\Document\\Areabrick\\AbstractTemplateAreabrick';
        if ($extendsCustom) {
            $files = File::files(sprintf('%s/src/Document/Areabrick/', getcwd()));
            $files = collect($files)->map(fn($file) => pathinfo($file)['filename'])->map(fn($file) => str_replace('.php', '', $file))->toArray();
            $extends = select(
                options: $files,
                label: 'Choose the areabrick you want to extend.',
                default: 'AbstractTemplateAreabrick',
            );

        }

        File::put(
            path: sprintf('%s/%s.php', $areabrickPath, $areabrick),
            contents: View::make('PimcorePlugin::areabrick.areabrick', [
                'class' => $areabrick,
                'name' => Str::title($areabrick),
                'namespace' => $areabrickNamespace,
                'view' => $viewPath,
                'extends' => $extends,
                'extendsCustom' => $extendsCustom
            ])->render()
        );
        $this->info(sprintf('Areabrick created at <fg=yellow>%s%s.php</>', $areabrickPath, $areabrick));

        $this->createView($areabrick);
    }

    protected function createView(string $areabrick): void
    {
        $viewPath = sprintf('%s/templates/areas/%s/', getcwd(), $this->getViewName($areabrick));
        File::ensureDirectoryExists($viewPath);

        $viewPath = sprintf('%s/templates/areas/%s/view.html.twig', getcwd(), $this->getViewName($areabrick));

        if (File::exists($viewPath)) {
            $overwrite = confirm(
                label: sprintf(
                    'Areabrick view already exists at %s Do you want to overwrite?',
                    $viewPath
                ),
                default: false,
                yes: 'Overwrite',
                no: 'Continue without overwriting'
            );

            if (!$overwrite) {
                return;
            }
        }

        $template = View::make('PimcorePlugin::areabrick.view', [
            'areabrick' => $areabrick,
            'title' => Str::title($areabrick),
        ])->render();
        File::put(
            path: sprintf('%s', $viewPath),
            contents: $template
        );
        $this->info(sprintf('Areabrick template created at <fg=yellow>%s</>', $viewPath));
    }

    private function getViewName(string $areabrick): string
    {
        return Str::snake($areabrick, '-');
    }
}

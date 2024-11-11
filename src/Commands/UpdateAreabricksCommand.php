<?php

declare(strict_types=1);

namespace Forge\Plugins\Pimcore\Commands;

use Forge\Plugins\Pimcore\Visitors\AddNullReturnVisitor;
use Forge\Plugins\Pimcore\Visitors\ReturnTypeVisitor;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;

class UpdateAreabricksCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'pimcore:areabrick:update {path} {--fix} {--rules=*}';
    protected $description = 'Updates areabricks according to pimcore 11 specification';

    public function handle(): int
    {
        $path = getcwd().'/'.$this->argument('path');
        $fix = $this->option('fix');

        $files = glob($path.'/*.php');

        $message = sprintf(
            '<fg=yellow>This is a destructive operation. It will modify %s source files inside %s. Do you want to continue?</>',
            count($files),
            $path
        );

        $confirm = confirm($message);

        if (!$confirm) {
            return 0;
        }

        $progress = progress(
            label: 'Updating areabricks',
            steps: $files,
            callback: function ($file, $progress) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $progress->label('Processing areabrick: '.$filename);

                return $this->updateAreabrick($file, $filename, $this->option('rules'), $this->option('fix'));
            }
        );

        return 0;
    }

    protected function updateAreabrick(string $file, string $name, array $rules = ['@Symfony'], bool $fix = false)
    {
        $content = file_get_contents($file);

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($content);

        $nodeTraverser = new NodeTraverser();

        // Change return type of action method to ?\Symfony\Component\HttpFoundation\Response
        $nodeTraverser->addVisitor(new ReturnTypeVisitor(
            command: $this,
            methodName: 'action',
            existingReturnType: 'void',
            newReturnType: '?\\Symfony\\Component\\HttpFoundation\\Response',
            nodeType: ClassMethod::class
        ));

        // Add `return null;` to action method if it does not have a return statement as last statement in the method
        $nodeTraverser->addVisitor(new AddNullReturnVisitor(
            command: $this,
            methodName: 'action',
            nodeType: ClassMethod::class
        ));

        $ast = $nodeTraverser->traverse($ast);

        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();

        $newContent = $prettyPrinter->prettyPrintFile($ast);

        file_put_contents($file, $newContent);

        if ($fix) {
            $this->info('Applying CS Fixer to areabrick: '.$name);
            $rules = implode(',', $rules);
            $process = new \Symfony\Component\Process\Process(['php-cs-fixer', 'fix', '--rules='.$rules, $file]);
            $process->mustRun();
        }

        return $file;
    }
}

<?php

declare(strict_types=1);

namespace Forge\Plugins\Pimcore\Visitors;

use Illuminate\Console\Command;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;

class ReturnTypeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        protected Command $command,
        protected string $methodName,
        protected string $existingReturnType,
        protected string $newReturnType,
        protected string $nodeType,
    ) {
    }

    public function enterNode(Node $node)
    {
        if (!$node instanceof Function_ && !$node instanceof ClassMethod) {
            return;
        }

        $name = $node->name->name;

        if ($name !== $this->methodName) {
            return;
        }

        $returnType = $node->getReturnType();

        $returnTypeName = !empty($returnType) ? $returnType->toString() : null;

        if ($returnTypeName !== $this->existingReturnType) {
            return;
        }

        $node->returnType = new Node\Name($this->newReturnType);
    }
}

<?php

declare(strict_types=1);

namespace Forge\Plugins\Pimcore\Visitors;

use Illuminate\Console\Command;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;

class AddNullReturnVisitor extends NodeVisitorAbstract
{
    public function __construct(
        protected Command $command,
        protected string $methodName,
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
        $statements = $node->stmts;
        $lastStatement = end($statements);

        if ($lastStatement instanceof Node\Stmt\Return_) {
            return;
        }

        $node->stmts[] = new Node\Stmt\Return_(
            new Node\Expr\ConstFetch(
                new Node\Name('null')
            )
        );
    }
}

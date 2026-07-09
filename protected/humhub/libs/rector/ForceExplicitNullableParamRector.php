<?php

namespace humhub\libs\rector;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ForceExplicitNullableParamRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [Param::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add explicit nullable type (?Type) when parameter default value is null',
            [
                new CodeSample(
                    <<<'CODE'
function test(Type $param = null) {}
CODE
                    ,
                    <<<'CODE'
function test(?Type $param = null) {}
CODE,
                ),
            ],
        );
    }

    public function refactor(Node $node): ?Node
    {
        // Type must exist and be a Node
        if (!$node->type instanceof Node || $node->type instanceof Node\NullableType) {
            return null;
        }

        // Default value must be defined as `null`
        if (!$node->default instanceof Node || !$this->nodeComparator->areNodesEqual(
            $node->default,
            new Node\Expr\ConstFetch(new Node\Name('null')),
        )) {
            return null;
        }

        // Skip if the param is already nullable
        if ($node->type instanceof Node\UnionType) {
            foreach ($node->type->types as $subType) {
                if ($this->isName($subType, 'null') || $this->isName($subType, 'mixed')) {
                    return null;
                }
            }

            $node->type->types[] = new Node\Name('null');
            return $node;
        }

        // Convert `Type` to `?Type`
        $node->type = new NullableType($node->type);

        return $node;
    }
}

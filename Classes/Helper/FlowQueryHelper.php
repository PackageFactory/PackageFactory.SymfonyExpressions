<?php
declare(strict_types=1);

namespace PackageFactory\SymfonyExpressions\Helper;

use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class FlowQueryHelper
{
    public static function create(NodeInterface $node): FlowQuery
    {
        return new FlowQuery($node);
    }
}

<?php
declare(strict_types=1);

namespace PackageFactory\SymfonyExpressions\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use PackageFactory\SymfonyExpressions\Evaluator\SymfonyExpressionEvaluator;

class SymfonyExpressionImplementation extends AbstractFusionObject
{

    /**
     * @var SymfonyExpressionEvaluator
     * @Flow\Inject
     */
    protected $symfonyExpressionEvaluator;

    public function evaluate()
    {
        return $this->symfonyExpressionEvaluator->evaluate($this->fusionValue('expression'), $this->runtime->getCurrentContext());
    }
}

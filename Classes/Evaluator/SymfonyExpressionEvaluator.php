<?php
declare(strict_types=1);

namespace PackageFactory\SymfonyExpressions\Evaluator;

use Neos\Flow\Annotations as Flow;
use Neos\Cache\Frontend\VariableFrontend;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use PackageFactory\SymfonyExpressions\ExpressionFunctionProvider\HelperExpressionFunctionProvider;

/**
 * @Flow\Scope("singleton")
 */
class SymfonyExpressionEvaluator
{

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @Flow\InjectConfiguration(path="helpers")
     * @var array
     */
    protected $helperConfiguration;

    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $parsedExpressionCache;

    public function initializeObject()
    {
        $this->expressionLanguage = new ExpressionLanguage();
        foreach ($this->helperConfiguration as $name => $helperConfig) {
            $this->expressionLanguage->registerProvider(
                new HelperExpressionFunctionProvider($name, $helperConfig)
            );
        }
    }

    /**
     * @param string $expression
     * @param array $context
     * @return mixed
     */
    public function evaluate(string $expression, array $context)
    {
        $cacheIdentifier = md5($expression . ':' . implode(',',array_keys($context)));
        if ($parsedExpression = $this->parsedExpressionCache->get($cacheIdentifier)) {
            return $this->expressionLanguage->evaluate($parsedExpression, $context);
        }
        // this is needed as otherwise the parser will cast all nodes to string which crashes for complex objects
        $contextWithNullValues = array_map(function($item) { return null;}, $context);
        $parsedExpression = $this->expressionLanguage->parse($expression, $contextWithNullValues);
        $this->parsedExpressionCache->set($cacheIdentifier, $parsedExpression);
        return $this->expressionLanguage->evaluate($parsedExpression, $context);
    }
}

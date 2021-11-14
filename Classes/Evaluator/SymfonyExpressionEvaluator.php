<?php
declare(strict_types=1);

namespace PackageFactory\SymfonyExpressions\Evaluator;

use Neos\Flow\Annotations as Flow;
use Neos\Cache\Frontend\VariableFrontend;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * @Flow\Scope("singleton")
 */
class SymfonyExpressionEvaluator
{

    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $parsedExpressionCache;

    /**
     * @Flow\InjectConfiguration(path="helpers")
     * @var array
     */
    protected $helperConfiguration;

    /**
     * @Flow\InjectConfiguration(path="providers")
     * @var array
     */
    protected $providerConfiguration;

    /**
     * @Flow\InjectConfiguration(path="functions")
     * @var array
     */
    protected $functionConfiguration;

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @var array<string,mixed>
     */
    protected $defaultContextVariables = [];

    public function initializeObject()
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $functions = $this->createHelperFunctions($this->functionConfiguration);
        foreach($functions as $function) {
            $this->expressionLanguage->addFunction($function);
        }
        $providers = $this->createFunctionProviders($this->providerConfiguration);
        foreach($providers as $provider) {
            $this->expressionLanguage->registerProvider($provider);
        }
        $this->defaultContextVariables = $this->createDefaultContextVariables($this->helperConfiguration);
    }

    /**
     * Evaluate the given expression
     *
     * @param string $expression
     * @param array $context
     * @return mixed
     */
    public function evaluate(string $expression, array $context = [])
    {
        $mergedContext = array_merge($context, $this->defaultContextVariables);
        $cacheIdentifier = md5($expression . ':' . implode(',',array_keys($mergedContext)));
        if ($parsedExpression = $this->parsedExpressionCache->get($cacheIdentifier)) {
            return $this->expressionLanguage->evaluate($parsedExpression, $mergedContext);
        }
        $parsedExpression = $this->expressionLanguage->parse($expression, array_keys($mergedContext));
        $this->parsedExpressionCache->set($cacheIdentifier, $parsedExpression);
        return $this->expressionLanguage->evaluate($parsedExpression, $mergedContext);
    }

    /**
     * Create the ExpressionFunctions for the configured functions
     *
     * @see: https://symfony.com/doc/current/components/expression_language/extending.html
     * @param array $configuration
     * @return ExpressionFunction[]
     */
    public function createHelperFunctions(array $configuration): array
    {
        $expressionFunctions = [];
        foreach ($configuration as $expressionFunctionName => $functionPath) {
            if (strpos($functionPath, '::') !== false) {
                list($className, $methodName) = explode('::', $functionPath, 2);
                $compiler = function (...$args) use ($methodName, $className) {
                    return sprintf('\%s::%s(%s)', $className, $methodName, implode(', ', $args));
                };
                $evaluator = function ($p, ...$args) use ($methodName, $className) {
                    return $className::$methodName(...$args);
                };
                $expressionFunctions[] = new ExpressionFunction(
                    $expressionFunctionName,
                    $compiler,
                    $evaluator
                );
            } else {
                $expressionFunctions = ExpressionFunction::fromPhp($functionPath,$expressionFunctionName);
            }
        }
        return $expressionFunctions;
    }

    /**
     * Instantiate the configured ExpressionFunctionProviders
     *
     * @see: https://symfony.com/doc/current/components/expression_language/extending.html
     * @param array $configuration
     * @return ExpressionFunctionProviderInterface[]
     */
    public function createFunctionProviders(array $configuration): array
    {
        $expressionFunctionProviders = [];
        foreach ($configuration as $objectType) {
            if (is_null($objectType)) {
                continue;
            }
            $provider = new $objectType();
            if (!$provider instanceof ExpressionFunctionProviderInterface) {
                throw new \InvalidArgumentException(sprintf("Expression function provider %s does not implement %s", $objectType, ExpressionFunctionProviderInterface::class));
            }
            $expressionFunctionProviders[] = new $objectType();
        }
        return $expressionFunctionProviders;
    }

    /**
     * Get default context variables from configuration that should be set in the context of all expressions by default.
     *
     * @param array $configuration An one dimensional associative array of context variable paths mapping to object names
     * @return array Array with default context variable objects.
     */
    public function createDefaultContextVariables(array $configuration): array
    {
        $defaultContextVariables = [];
        foreach ($configuration as $variableName => $objectType) {
            $variablePathNames = explode('.', $variableName);
            $pathDepth = count($variablePathNames);
            $currentDepth = 1;
            $currentHelper = null;
            foreach ($variablePathNames as $pathName) {
                // directly assign key to context array
                if (($currentDepth === 1) && ($pathDepth === 1)) {
                    $defaultContextVariables[$pathName] = new $objectType();
                    continue;
                }

                // build tree of stdClass objects
                if ($currentDepth == 1) {
                    if (isset($defaultContextVariables[$pathName])) {
                        $currentHelper = $defaultContextVariables[$pathName];
                    } else {
                        $currentHelper = new \stdClass();
                        $defaultContextVariables[$pathName] = $currentHelper;
                    }
                } else {
                    if ($currentDepth < $pathDepth) {
                        if ($currentHelper->$pathName) {
                            $currentHelper = $currentHelper->$pathName;
                        } else {
                            $currentHelper->$pathName = new \stdClass();
                        }
                    } else {
                        $currentHelper->$pathName = new $objectType();
                    }
                }
                $currentDepth ++;
            }
        }
        return $defaultContextVariables;
    }
}

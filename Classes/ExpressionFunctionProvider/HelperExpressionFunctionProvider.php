<?php
declare(strict_types=1);

namespace PackageFactory\SymfonyExpressions\ExpressionFunctionProvider;

use PackageFactory\SymfonyExpressions\Helper\StringHelper;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class HelperExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{

    /**
     * @var string
     */
    protected $helper;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @param string $prefix
     * @param string $helper
     */
    public function __construct(string $prefix, string $helper)
    {
        if (class_exists($helper)) {
            $this->helper = $helper;
            $this->prefix = $prefix;
        } else {
            throw new \InvalidArgumentException(sprintf("Class helper %s does not exist!",  $helper));
        }
    }

    /**
     * Return a list of all statically callable methods of the given helper
     *
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        $expressionFunctions = [];
        $classFunctions = get_class_methods($this->helper);
        foreach ($classFunctions as $function) {
            if ($function == '__sleep' || $function == '__wakeup') {
                continue;
            }
            $name = $this->prefix . StringHelper::firstLetterToUpperCase($function);
            $helper = $this->helper;
            $compiler = function (...$args) use ($function, $helper) {
                return sprintf('\%s::%s(%s)', $helper, $function, implode(', ', $args));
            };
            $evaluator = function ($p, ...$args) use ($function, $helper) {
                return $helper::$function(...$args);
            };

            $expressionFunctions[] = new ExpressionFunction(
                $name,
                $compiler,
                $evaluator
            );
        }
        return $expressionFunctions;
    }
}

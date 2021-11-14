<?php
declare(strict_types=1);

namespace PackageFactory\SymfonyExpressions\Dsl;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\Core\DslInterface;

/**
 * Class Fusion SymfonyExpression Dsl
 *
 * @Flow\Scope("singleton")
 */
class SymfonyExpressionDslImplementation implements DslInterface
{

    /**
     * Transpile the given dsl-code to fusion-code
     *
     * @param string $code
     * @return string
     */
    public function transpile($code)
    {
        $codeInQuotes = addslashes($code);
        $fusionCode = <<<EOF
            PackageFactory.SymfonyExpressions:Expression {
                expression = "$codeInQuotes"
            }
            EOF;
        return $fusionCode;
    }
}

<?php
declare(strict_types=1);

namespace PackageFactory\SymfonyExpressions\Aspects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class FusionParserAspect
{
    const SYMFONY_EXPRESSION_SCAN_PATTERN = '/
			\${sx\:(?P<exp>
				(?:
					{ (?P>exp) }			# match object literal expression recursively
					|[^{}"\']+				# simple eel expression without quoted strings
					|"[^"\\\\]*				# double quoted strings with possibly escaped double quotes
						(?:
							\\\\.			# escaped character (quote)
							[^"\\\\]*		# unrolled loop following Jeffrey E.F. Friedl
						)*"
					|\'[^\'\\\\]*			# single quoted strings with possibly escaped single quotes
						(?:
							\\\\.			# escaped character (quote)
							[^\'\\\\]*		# unrolled loop following Jeffrey E.F. Friedl
						)*\'
				)*
			)}
			/x';

    /**
     * @Flow\Around("method(Neos\Fusion\Core\Parser->parse())")
     * @param JoinPointInterface $joinPoint The current join point
     * @return mixed
     */
    public function expandSymfonyExpressionsToFusion(JoinPointInterface $joinPoint)
    {
        $fusionCode = $joinPoint->getMethodArgument('sourceCode');

        if (preg_match(self::SYMFONY_EXPRESSION_SCAN_PATTERN, $fusionCode)) {
            $fusionCodeProcessed = preg_replace_callback(
                self::SYMFONY_EXPRESSION_SCAN_PATTERN,
                function ($matches) {
                    $expression = $matches[1];
                    $codeInQuotes = addslashes($expression);
                    $fusionCode = <<<EOF
                        PackageFactory.SymfonyExpressions:Expression {
                            expression = "$codeInQuotes"
                        }
                        EOF;
                    return $fusionCode;
                },
                $fusionCode
            );
            $joinPoint->setMethodArgument('sourceCode', $fusionCodeProcessed);
        }

        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }
}

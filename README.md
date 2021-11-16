# PackageFactory.SymfonyExpressions
## Symfony-expressions for Neos.Fusion

!!! This is an experimental prototype to validate the feasibility of using symfony-expressions in fusion as an alternative to eel. It should not be used in projects !!!

The symfony expressions language is documented by the symfomy project https://symfony.com/doc/current/components/expression_language.html 
and offers a very well defined syntax with operations that go beyond what eel offers https://symfony.com/doc/current/components/expression_language/syntax.html

## Usage 

In Neos symfony-expressions are written like eel expressions with a prefix of `${sx:...}`.
The most important deviation from eel is that Strings are concatenated via `~` like so `${"Hello" + "world"}` becomes `${sx: "Hello" ~ "world"}`.
The `sx:` prefix works in afx aswell ``afx`<tag atttribute={sx:...}>{sx:...}</tag>` ``.
Even the fluent syntax of FlowQuery is supported `${sx:q(node).closest('[instanceof Neos.Neos:Document]').property("title")}`.

```
# use as expression prefix
exampleOne = ${sx:...}

# use as fusion dsl
exampleTwo = sx`...`

# use expression prefix in afx
renderer = afx`
    <div data-example={sx:...}>
        {sx:...}
    </div>
`
```

The `sx` prefix is also registered as dsl so `${sx:...}` is equivalent to ``sx`...` ``. While this is technically cleaner it does
not work inside of afx.

Note: The `sx:` prefix was the easiest way to implement a working prototype for Fusion and Afx. 
If this ever becomes a language feature this should be discussed in detail and probably another solution will be found. 

## Extensibility: Helpers, Function and Providers

The package allows to register functions, function providers and helpers which allows to mimic the behavior of eel in most parts.
```
PackageFactory:
  SymfonyExpressions:

    #
    # Class names of helper objects that should always be present in the expression context.
    #
    helpers:
      String: 'Neos\Eel\Helper\StringHelper'
      Array: 'Neos\Eel\Helper\ArrayHelper'
      Date: 'Neos\Eel\Helper\DateHelper'
      Configuration: 'Neos\Eel\Helper\ConfigurationHelper'
      Math: 'Neos\Eel\Helper\MathHelper'
      Json: 'Neos\Eel\Helper\JsonHelper'
      Security: 'Neos\Eel\Helper\SecurityHelper'
      Translation: 'Neos\Flow\I18n\EelHelper\TranslationHelper'
      StaticResource: 'Neos\Flow\ResourceManagement\EelHelper\StaticResourceHelper'
      Type: 'Neos\Eel\Helper\TypeHelper'
      I18n: 'Neos\Flow\I18n\EelHelper\TranslationHelper'
      File: 'Neos\Eel\Helper\FileHelper'
      BaseUri: 'Neos\Fusion\Eel\BaseUriHelper'

    #
    # Class names of function providers implementing the ExpressionFunctionProviderInterface
    # @see: https://symfony.com/doc/current/components/expression_language/extending.html
    #
    providers: []

    #
    # Functions that are either static functions with namespaces or static methods of classes 
    # - static class method  `name: 'Name\Space::method'`
    # - static function `name: 'Name\Space\method'``
    # 
    functions:
      q: 'Neos\Eel\FlowQuery\FlowQuery::q'

```

## Installation

For now the package is available via github only. https://github.com/PackageFactory/PackageFactory.SymfonyExpressions
If you want to participate in the experiment add this to your `composer.json`

```
{
  "require": {    
    "packagefactory/symfonyexpressions": "@dev"
  },
  "repositories": {
    "symfonyexpressions": {
      "type": "vcs",
      "url": "https://github.com/PackageFactory/PackageFactory.SymfonyExpressions"
    } 
  }
}
```

## Inner workings

The package works via an Aspect of the Fusion parser that detects eel-expressions which start as `${sx:` and renders a fusion
prototype instead of the eel expression. 

The fusion code:
```
value = ${sx:"hello" ~ " world"}
```
is transpiled as:
```
value = PackageFactory.SymfonyExpressions:Expression {
    expression = "\"hello\" ~ \" world\""
}
```

The fusion prototype then parses and evaluates expression with the current fusion context. 
The parsed expressions are cached in the `PackageFactory_SymfonyExpressions_Expressions` cache for reuse if the same expression is 
evaluated again at a later point in time.
 
The expression cache can be flushed via `./flow flow:cache:flushone PackageFactory_SymfonyExpressions_Expressions`.

## Limitations
 
The most important limitations we are currently aware of are:
 
- The implementation via a Fusion object may or may not prove problematic especially performance wise.
- Multiline expressions will not work (yet)
- The ContextAware interface and other security limitations of eel are not on place (yet). 
- The parsed Expressions are cached but for now the cache is never flushed.

## Contribution
 
We will gladly accept contributions. Please send us pull requests.


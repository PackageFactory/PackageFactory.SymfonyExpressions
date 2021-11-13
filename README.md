# PackageFactory.SymfonyExpressions
## Symfony-expressions for Neos.Fusion

!!! This is an experimental prototype to validate the feasibility of using symfony-expressions in fusion as an alternative to eel. It should not be used in projects !!!

## Usage 

The symfony expressions language is documented by the symfomy project https://symfony.com/doc/current/components/expression_language.html
There are many differences and similarities to the eel language of which the most notable ones ones are:

- Strings are concatenated via `~`
- Helper are functions not Objects 

In Neos symfony-expressions are written like eel expressions with a prefix of `${sx:...}`. 
This prefix works in afx aswell ``afx`<tag atttribute={sx:...}>{sx:...}</tag>` ``. 

Note: The `sx:` prefix was the easiest way to implement a working prototype for Fusion and Afx. If this ever becomes a 
language feature this should be discussed in detail and probably another solution will be found. 

## Helpers

Similar to eel the symfony expression language can be extended but other than eel extensions for symfony expression are functions. 
We added a function provider that allows to register helper classes that provide static helper functions. All functions of the 
helper classes are registered to the symfony expression language by combining the given prefix with the function name using CamelCase.

The example demonstrates the use of the `toUpperCase` function in the `String` helper.
```
renderer = afx`
    <div>
        {sx:StringToUpperCase("hello world")}
    </div>
`
```

The package contains some ports of eel helpers to test the extensibility: 
```
PackageFactory:
  SymfonyExpressions:
    helpers:
      
      #
      # Those helper are port of eel helpers with static methods
      #
      String: 'PackageFactory\SymfonyExpressions\Helper\StringHelper'
      Array: 'PackageFactory\SymfonyExpressions\Helper\ArrayHelper'
      Date: 'PackageFactory\SymfonyExpressions\Helper\DateHelper'
      
      #
      # Not working yet, how do we define fluent interfaces ;-/ 
      # 
      FlowQuery: 'PackageFactory\SymfonyExpressions\Helper\FlowQueryHelper'
```

Note: We are still looking for a way of defining fluent interfaces like flowQuery for symfony expressions.

## Installation

For now the package is available via github only. https://github.com/PackageFactory/PackageFactory.SymfonyExpressions
If you want to try participate in the experiment add this to your `composer.json`

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
value = ${sc:"hello world"}
```
is transpiled as:
```
value = PackageFactory.SymfonyExpressions:Expression {
    expression = "$codeInQuotes"
}
```

The fusion prototype then parses and evaluates expression with the current fusion context. 
The parsed expressions are cached in the `PackageFactory_SymfonyExpressions_Expressions` cache for reuse if the same expression is 
evaluated again at a later point in time.
 
The expression cache can be flushed via `./flow flow:cache:flushone PackageFactory_SymfonyExpressions_Expressions`.

## Limitations
 
The most important limitations we are currently aware of are:
 
- The implementation via a Fusion object may or may not prove problematic especially performance wise. 
- The missing support (or finding the correct syntax) for fluent interfaces limits the actual use.
- The parsed Expressions are cached but for now the cache is never flushed.

## Contribution
 
We will gladly accept contributions. Please send us pull requests.


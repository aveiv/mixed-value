[![Build Status](https://travis-ci.com/aveiv/mixed-value.svg?branch=master)](https://travis-ci.com/aveiv/mixed-value) [![Latest Stable Version](https://poser.pugx.org/aveiv/mixed-value/v)](//packagist.org/packages/aveiv/mixed-value) [![Total Downloads](https://poser.pugx.org/aveiv/mixed-value/downloads)](//packagist.org/packages/aveiv/mixed-value) [![License](https://poser.pugx.org/aveiv/mixed-value/license)](//packagist.org/packages/aveiv/mixed-value)

MixedValue provides easy array access and the ability to convert/cast values.

## Installation

```
composer require aveiv/mixed-value
```

## Access to array

```php
$mixed = new MixedValue([
    'good' => [
        'path' => 'value',
    ]
]);

$mixed['good']['path']->getValue(); // returns "value"
$mixed['invalid']['path']->getValue(); // throws MissingValueException

$mixed['invalid']['path']->findValue(); // returns null
$mixed['invalid']['path']->findValue() ?? 'default'; // returns "default"
```

## Checking value types

```php
$mixed = new MixedValue([
    'array_val' => [],
    'bool_val' => true,
    'float_val' => 1.0,
    'int_val' => 1,
    'numeric_val' => '99.99',
    'str_val' => 'string',
]);

$mixed['array_val']->isArray()->getValue(); // returns []
$mixed['bool_val']->isBool()->getValue(); // returns true
$mixed['float_val']->isFloat()->getValue(); // returns 1.0
$mixed['int_val']->isInt()->getValue(); // returns 1
$mixed['numeric_val']->isNumeric()->getValue(); // returns '99.99'
$mixed['str_val']->isString()->getValue(); // returns "string"

$mixed['str_val']->isInt()->getValue(); // throws UnexpectedValueException
```

## Converting/casting values

Default converters use PHP casting rules. UnexpectedValueException is thrown if a value cannot be converted.

```php
$mixed = new MixedValue([
    'id' => '99',
    'name' => 'Mary',
    'birthdate' => '1990-01-01',
    'balance' => '999.99',
    'isActive' => 1,
    
    'array_data' => [],
]);

$mixed['id']->toInt()->getValue(); // returns 99
$mixed['name']->toString()->getValue(); // returns "Mary"
$mixed['birthdate']->toDateTime()->getValue(); // returns DateTime("1990-01-01")
$mixed['balance']->toFloat()->getValue(); // returns 999.99
$mixed['isActive']->toBool()->getValue(); // returns true

$mixed['array_data']->toString()->getValue(); // throws UnexpectedValueException
```

## Processing array elements

```php
$mixed = new MixedValue([
    'mixed_arr' => [1, 2, '3', 4, 5],
]);

$mixed['mixed_arr']
    ->map(fn(MixedValue $el) => $el->toInt()->getValue())
    ->getValue(); // returns [1, 2, 3, 4, 5]
```

## Use custom value processors

```php
class StripSpacesProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        if (!is_string($value)) {
            throw new UnexpectedValueException('Value must be a string');
        }
        return str_replace(' ', '', $value);
    }
}

$mixed = new MixedValue([
    'bad_float' => '9 999.99',
]);
$mixed->registerValueProcessor('stripSpaces', new StripSpacesProcessor());

$mixed['bad_float']->stripSpaces()->toFloat()->getValue(); // return 9999.99
```

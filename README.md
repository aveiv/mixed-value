ArrayReader provides easy access to array and the ability to convert/cast its values.

## Installation

```
composer require aveiv/array-reader
```

## Access to array

```php
$reader = new ArrayReader([
    'good' => [
        'path' => 'value',
    ]
]);

$reader['good']['path']->getValue(); // returns "value"
$reader['invalid']['path']->getValue(); // throws MissingValueException

$reader['invalid']['path']->findValue(); // returns null
$reader['invalid']['path']->findValue() ?? 'default'; // returns "default"
```

## Checking value types

```php
$reader = new ArrayReader([
    'array_val' => [],
    'bool_val' => true,
    'float_val' => 1.0,
    'int_val' => 1,
    'str_val' => 'string',
]);

$reader['array_val']->isArray()->getValue(); // returns []
$reader['bool_val']->isBool()->getValue(); // returns true
$reader['float_val']->isFloat()->getValue(); // returns 1.0
$reader['int_val']->isInt()->getValue(); // returns 1
$reader['str_val']->isString()->getValue(); // returns "string"

$reader['str_val']->isInt()->getValue(); // throws UnexpectedValueException
```

## Converting values

Default converters use PHP casting rules. UnexpectedValueException is thrown if a value cannot be converted.

```php
$reader = new ArrayReader([
    'id' => '99',
    'name' => 'Mary',
    'birthdate' => '1990-01-01',
    'balance' => '999.99',
    'isActive' => 1,
    
    'array_data' => [],
]);

$reader['id']->toInt()->getValue(); // returns 99
$reader['name']->toString()->getValue(); // returns "Mary"
$reader['birthdate']->toDateTime()->getValue(); // returns DateTime("1990-01-01")
$reader['balance']->toFloat()->getValue(); // returns 999.99
$reader['isActive']->toBool()->getValue(); // returns true

$reader['array_data']->toString()->getValue(); // throws UnexpectedValueException
```

## Use custom converters

```php
class StripSpacesConverter implements ConverterInterface
{
    public function __invoke($value)
    {
        if (!is_string($value)) {
            throw new UnexpectedValueException('Value must be a string');
        }
        return str_replace(' ', '', $value);
    }
}

$reader = new ArrayReader([
    'bad_float' => '9 999.99',
]);
$reader->registerConverter('stripSpaces', new StripSpacesConverter());

$reader['bad_float']->stripSpaces()->toFloat()->getValue(); // return 9999.99
```

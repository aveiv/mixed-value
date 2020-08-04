<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Converter;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class StringConverter implements ConverterInterface
{
    public function __invoke($value)
    {
        if (is_array($value)) {
            throw new UnexpectedValueException('Value cannot be an array');
        }
        if (is_object($value) && !method_exists($value, '__toString')) {
            throw new UnexpectedValueException('Value must implement the __toString method');
        }
        /** @psalm-var scalar|object $value */
        return strval($value);
    }
}

<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Converter;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class IsStringConverter implements ConverterInterface
{
    public function __invoke($value)
    {
        if (!is_string($value)) {
            throw new UnexpectedValueException('Value must be a string');
        }
        return $value;
    }

}

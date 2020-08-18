<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Converter;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class IsBoolConverter implements ConverterInterface
{
    public function __invoke($value)
    {
        if (!is_bool($value)) {
            throw new UnexpectedValueException('Value must be a boolean');
        }
        return $value;
    }
}

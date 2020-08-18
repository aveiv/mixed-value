<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Converter;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class IsFloatConverter implements ConverterInterface
{
    public function __invoke($value)
    {
        if (!is_float($value)) {
            throw new UnexpectedValueException('Value must be a float');
        }
        return $value;
    }
}

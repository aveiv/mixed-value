<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Converter;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class IsArrayConverter implements ConverterInterface
{
    public function __invoke($value)
    {
        if (!is_array($value)) {
            throw new UnexpectedValueException('Value must be an array');
        }
        return $value;
    }
}

<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Converter;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class IsIntConverter implements ConverterInterface
{
    public function __invoke($value)
    {
        if (!is_int($value)) {
            throw new UnexpectedValueException('Value must be an int');
        }
        return $value;
    }
}

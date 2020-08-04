<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Converter;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class IntConverter implements ConverterInterface
{
    public function __invoke($value)
    {
        if (is_object($value)) {
            throw new UnexpectedValueException('Value cannot be an object');
        }
        return intval($value);
    }
}

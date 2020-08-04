<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Converter;

final class BoolConverter implements ConverterInterface
{
    public function __invoke($value)
    {
        return boolval($value);
    }
}

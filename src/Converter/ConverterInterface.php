<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Converter;

interface ConverterInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function __invoke($value);
}

<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\ValueProcessor;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class IsIntProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        if (!is_int($value)) {
            throw new UnexpectedValueException('Value must be an int');
        }
        return $value;
    }
}

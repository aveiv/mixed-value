<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\ValueProcessor;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class IsStringProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        if (!is_string($value)) {
            throw new UnexpectedValueException('Value must be a string');
        }
        return $value;
    }

}

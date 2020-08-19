<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\ValueProcessor;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class IsBoolProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        if (!is_bool($value)) {
            throw new UnexpectedValueException('Value must be a boolean');
        }
        return $value;
    }
}

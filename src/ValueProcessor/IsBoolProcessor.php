<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\ValueProcessor;

use Aveiv\MixedValue\Exception\UnexpectedValueException;

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

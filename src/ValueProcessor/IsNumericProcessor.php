<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\ValueProcessor;

use Aveiv\MixedValue\Exception\UnexpectedValueException;

final class IsNumericProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        if (!is_numeric($value)) {
            throw new UnexpectedValueException('Value must be numeric');
        }
        return $value;
    }
}

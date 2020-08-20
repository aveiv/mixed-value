<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\ValueProcessor;

use Aveiv\MixedValue\Exception\UnexpectedValueException;

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

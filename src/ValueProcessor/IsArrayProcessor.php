<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\ValueProcessor;

use Aveiv\MixedValue\Exception\UnexpectedValueException;

final class IsArrayProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        if (!is_array($value)) {
            throw new UnexpectedValueException('Value must be an array');
        }
        return $value;
    }
}

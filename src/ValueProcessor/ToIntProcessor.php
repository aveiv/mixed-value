<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\ValueProcessor;

use Aveiv\MixedValue\Exception\UnexpectedValueException;

final class ToIntProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        if (is_object($value)) {
            throw new UnexpectedValueException('Value cannot be an object');
        }
        return intval($value);
    }
}

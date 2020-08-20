<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\ValueProcessor;

use Aveiv\MixedValue\Exception\UnexpectedValueException;

final class ToStringProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        if (is_array($value)) {
            throw new UnexpectedValueException('Value cannot be an array');
        }
        if (is_object($value) && !method_exists($value, '__toString')) {
            throw new UnexpectedValueException('Value must implement the __toString method');
        }
        /** @psalm-var scalar|object $value */
        return strval($value);
    }
}

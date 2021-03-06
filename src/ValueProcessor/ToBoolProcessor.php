<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\ValueProcessor;

final class ToBoolProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        return boolval($value);
    }
}

<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\ValueProcessor;

use Aveiv\ArrayReader\Exception\UnexpectedValueException;

final class ToDateTimeProcessor implements ValueProcessorInterface
{
    public function __invoke($value)
    {
        if (!is_string($value)) {
            throw new UnexpectedValueException('Value must be a string');
        }
        $dt = date_create($value);
        if ($dt === false) {
            throw new UnexpectedValueException('Failed to parse datetime string');
        }
        return $dt;
    }
}

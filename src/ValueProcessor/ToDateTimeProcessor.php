<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\ValueProcessor;

use Aveiv\MixedValue\Exception\UnexpectedValueException;

final class ToDateTimeProcessor implements ValueProcessorInterface
{
    private bool $immutable;

    public function __construct(bool $immutable = false)
    {
        $this->immutable = $immutable;
    }

    public function __invoke($value)
    {
        if (!is_string($value)) {
            throw new UnexpectedValueException('Value must be a string');
        }
        $dt = $this->immutable ? date_create_immutable($value) : date_create($value);
        if ($dt === false) {
            throw new UnexpectedValueException('Failed to parse datetime string');
        }
        return $dt;
    }
}

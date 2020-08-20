<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\ValueProcessor;

interface ValueProcessorInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function __invoke($value);
}

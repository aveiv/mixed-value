<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\Exception;

final class MissingValueException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $value)
    {
        $msg = sprintf('Value "%s" does not exists', $value);
        parent::__construct($msg);
    }
}

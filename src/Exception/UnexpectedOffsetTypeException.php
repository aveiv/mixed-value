<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Exception;

final class UnexpectedOffsetTypeException extends \RuntimeException implements ExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Offset must be a string or an integer');
    }
}

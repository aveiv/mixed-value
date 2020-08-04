<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Exception;

final class UndefinedMethodException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $class, string $method)
    {
        $msg = sprintf('Call to undefined method %s::%s()', $class, $method);
        parent::__construct($msg);
    }
}

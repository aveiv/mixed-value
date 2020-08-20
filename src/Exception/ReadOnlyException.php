<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\Exception;

use Aveiv\MixedValue\MixedValue;

final class ReadOnlyException extends \RuntimeException implements ExceptionInterface
{
    public function __construct()
    {
        $msg = sprintf('%s is readonly', MixedValue::class);
        parent::__construct($msg);
    }
}

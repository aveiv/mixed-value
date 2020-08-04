<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Exception;

use Aveiv\ArrayReader\ArrayReader;

final class ReadOnlyException extends \RuntimeException implements ExceptionInterface
{
    public function __construct()
    {
        $msg = sprintf('%s is readonly', ArrayReader::class);
        parent::__construct($msg);
    }
}

<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Exception;

use RuntimeException;
use Throwable;

final class CannotAcquireLockException extends RuntimeException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('', 0, $previous);
    }
}

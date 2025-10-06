<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Exception;

use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class InvalidEntityException extends RuntimeException
{
    /** @var ConstraintViolationListInterface|null */
    private ?ConstraintViolationListInterface $errors;

    public function __construct(
        ?ConstraintViolationListInterface $errors = null,
        Throwable $previous = null
    ) {
        parent::__construct('Invalid entity', 0, $previous);
        $this->errors = $errors;
    }

    /** @return ConstraintViolationInterface[] */
    public function getErrors(): iterable
    {
        return $this->errors->count() ? $this->errors : [];
    }
}

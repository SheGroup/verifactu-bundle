<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Model;

final class Response
{
    private string $status;
    private ?string $csv;
    private ?int $errorCode;
    private ?string $errorMessage;

    public function __construct(string $status, ?string $csv, ?int $errorCode, ?string $errorMessage)
    {
        $this->status = $status;
        $this->csv = $csv;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getCsv(): ?string
    {
        return $this->csv;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function isSuccess(): bool
    {
        return 'Correcto' === $this->status;
    }
}

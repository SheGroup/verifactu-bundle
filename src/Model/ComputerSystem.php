<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Model;

final class ComputerSystem
{
    private bool $production;
    private string $id;
    private string $name;
    private string $vendorId;
    private string $vendorName;
    private string $version;
    private string $installationNumber;
    private bool $onlySupportsVerifactu;
    private bool $supportsMultiplePayers;
    private bool $hasMultipleTaxpayers;

    /** @SuppressWarnings(PHPMD.ExcessiveParameterList) */
    public function __construct(
        bool $production,
        string $id,
        string $name,
        string $vendorId,
        string $vendorName,
        string $version,
        string $installationNumber,
        bool $onlySupportsVerifactu,
        bool $supportsMultiplePayers,
        bool $hasMultipleTaxpayers
    ) {
        $this->production = $production;
        $this->id = $id;
        $this->name = $name;
        $this->vendorId = $vendorId;
        $this->vendorName = $vendorName;
        $this->version = $version;
        $this->installationNumber = $installationNumber;
        $this->onlySupportsVerifactu = $onlySupportsVerifactu;
        $this->supportsMultiplePayers = $supportsMultiplePayers;
        $this->hasMultipleTaxpayers = $hasMultipleTaxpayers;
    }

    public function getVendorName(): string
    {
        return $this->vendorName;
    }

    public function getVendorId(): string
    {
        return $this->vendorId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getInstallationNumber(): string
    {
        return $this->installationNumber;
    }

    public function getOnlySupportsVerifactu(): bool
    {
        return $this->onlySupportsVerifactu;
    }

    public function getSupportsMultiplePayers(): bool
    {
        return $this->supportsMultiplePayers;
    }

    public function getHasMultipleTaxpayers(): bool
    {
        return $this->hasMultipleTaxpayers;
    }

    public function getApiDomain(): string
    {
        return $this->isProduction() ? 'https://www1.agenciatributaria.gob.es' : 'https://prewww1.aeat.es';
    }

    public function isProduction(): bool
    {
        return $this->production;
    }
}

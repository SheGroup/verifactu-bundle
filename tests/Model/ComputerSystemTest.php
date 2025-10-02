<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use SheGroup\VerifactuBundle\Model\ComputerSystem;

class ComputerSystemTest extends TestCase
{
    private const ID = 'TS456';
    private const NAME = 'Test System';
    private const VENDOR_ID = 'TV123';
    private const VENDOR_NAME = 'Test Vendor';
    private const VERSION = '1.0.0';
    private const INSTALLATION_NUMBER = 'IN789';
    private const ONLY_SUPPORTS_VERIFACTU = true;
    private const SUPPORTS_MULTIPLE_PAYERS = false;
    private const HAS_MULTIPLE_TAXPAYERS = true;

    public function testArguments(): void
    {
        $computerSystem = $this->createComputerSystem(false);

        $this->assertEquals(self::ID, $computerSystem->getId());
        $this->assertEquals(self::NAME, $computerSystem->getName());
        $this->assertEquals(self::VENDOR_ID, $computerSystem->getVendorId());
        $this->assertEquals(self::VENDOR_NAME, $computerSystem->getVendorName());
        $this->assertEquals(self::VERSION, $computerSystem->getVersion());
        $this->assertEquals(self::INSTALLATION_NUMBER, $computerSystem->getInstallationNumber());
        $this->assertEquals(self::ONLY_SUPPORTS_VERIFACTU, $computerSystem->getOnlySupportsVerifactu());
        $this->assertEquals(self::SUPPORTS_MULTIPLE_PAYERS, $computerSystem->getSupportsMultiplePayers());
        $this->assertEquals(self::HAS_MULTIPLE_TAXPAYERS, $computerSystem->getHasMultipleTaxpayers());
        $this->assertFalse($computerSystem->isProduction());
    }

    public function testGetApiDomainForProduction(): void
    {
        $computerSystem = $this->createComputerSystem(true);

        $domain = $computerSystem->getApiDomain();

        $this->assertEquals('https://www1.agenciatributaria.gob.es', $domain);
    }

    public function testGetApiDomainForNonProduction(): void
    {
        $computerSystem = $this->createComputerSystem(false);

        $domain = $computerSystem->getApiDomain();

        $this->assertEquals('https://prewww1.aeat.es', $domain);
    }

    private function createComputerSystem(bool $isProduction): ComputerSystem
    {
        return new ComputerSystem(
            $isProduction,
            self::ID,
            self::NAME,
            self::VENDOR_ID,
            self::VENDOR_NAME,
            self::VERSION,
            self::INSTALLATION_NUMBER,
            self::ONLY_SUPPORTS_VERIFACTU,
            self::SUPPORTS_MULTIPLE_PAYERS,
            self::HAS_MULTIPLE_TAXPAYERS
        );
    }
}

<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class QrCodeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('verifactu_qr', [QrCodeExtensionRuntime::class, 'generateQr']),
            new TwigFilter('verifactu_link_qr', [QrCodeExtensionRuntime::class, 'generateQrFromLink']),
        ];
    }
}

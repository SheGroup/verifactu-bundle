<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Twig\Extension;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Service\InvoiceLinkGenerator;
use Twig\Extension\RuntimeExtensionInterface;

final class QrCodeExtensionRuntime implements RuntimeExtensionInterface
{
    private InvoiceLinkGenerator $linkGenerator;

    public function __construct(InvoiceLinkGenerator $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;
    }

    public function generateQr(Invoice $invoice): ?string
    {
        $link = $this->linkGenerator->generate($invoice);

        return $link ? $this->generateQrFromLink($link) : null;
    }

    public function generateQrFromLink(string $link): ?string
    {
        /* @noinspection SpellCheckingInspection */
        $options = new QROptions([
            'outputType' => 'png',
            'addQuietzone' => false,
            'eccLevel' => EccLevel::M,
        ]);

        return (new QRCode($options))->render($link);
    }
}

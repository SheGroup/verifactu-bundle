<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Twig\Extension;

use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Service\InvoiceLinkGenerator;
use Twig\Extension\RuntimeExtensionInterface;

final class InvoiceLinkExtensionRuntime implements RuntimeExtensionInterface
{
    private InvoiceLinkGenerator $linkGenerator;

    public function __construct(InvoiceLinkGenerator $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;
    }

    public function generateLink(Invoice $invoice): ?string
    {
        return $this->linkGenerator->generate($invoice);
    }
}

<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class InvoiceLinkExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('verifactu_link', [InvoiceLinkExtensionRuntime::class, 'generateLink']),
        ];
    }
}

<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Serializer\Normalizer;

use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Service\InvoiceXmlGenerator;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class InvoiceXmlNormalizer implements NormalizerInterface
{
    private InvoiceXmlGenerator $invoiceXmlGenerator;

    public function __construct(InvoiceXmlGenerator $invoiceXmlGenerator)
    {
        $this->invoiceXmlGenerator = $invoiceXmlGenerator;
    }

    /** @param string|null $format */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return XmlEncoder::FORMAT === $format && $data instanceof Invoice;
    }

    /**
     * @param Invoice $object
     * @param string|null $format
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return $this->invoiceXmlGenerator->normalize($object);
    }
}

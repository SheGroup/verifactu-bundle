<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Entity\InvoiceLine;
use SheGroup\VerifactuBundle\Entity\InvoiceRecipient;
use SheGroup\VerifactuBundle\Enum\InvoiceType;
use SheGroup\VerifactuBundle\Model\ComputerSystem;
use SheGroup\VerifactuBundle\Serializer\Normalizer\InvoiceXmlNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

final class AEATInvoiceXmlGenerator implements InvoiceXmlGenerator
{
    private ComputerSystem $computerSystem;
    private NumberFormatter $numberFormatter;

    public function __construct(ComputerSystem $computerSystem, NumberFormatter $numberFormatter)
    {
        $this->computerSystem = $computerSystem;
        $this->numberFormatter = $numberFormatter;
    }

    public function generate(Invoice $invoice): string
    {
        $serializer = new Serializer([new InvoiceXmlNormalizer($this)], [new XmlEncoder()]);
        /* @noinspection SpellCheckingInspection */
        $xmlBody = $serializer->serialize($invoice, XmlEncoder::FORMAT, [
            'xml_root_node_name' => 'soapenv:Envelope',
            'xml_encoding' => 'UTF-8',
        ]);

        return trim($this->addNamespaces($xmlBody));
    }

    public function normalize(Invoice $invoice): array
    {
        /* @noinspection SpellCheckingInspection */
        return [
            'soapenv:Header' => null,
            'soapenv:Body' => $this->normalizeBody($invoice),
        ];
    }

    private function addNamespaces(string $xmlBody): string
    {
        $wsSumFolder = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones';
        /* @noinspection SpellCheckingInspection */
        $namespaces = [
            'xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"',
            sprintf('xmlns:sum="%s/es/aeat/tike/cont/ws/SuministroLR.xsd"', $wsSumFolder),
            sprintf('xmlns:sum1="%s/es/aeat/tike/cont/ws/SuministroInformacion.xsd"', $wsSumFolder),
        ];

        /* @noinspection SpellCheckingInspection */
        return preg_replace(
            '/<soapenv:Envelope/',
            sprintf('<soapenv:Envelope %s', implode(' ', $namespaces)),
            $xmlBody,
            1
        );
    }

    private function normalizeBody(Invoice $invoice): array
    {
        $recipients = array_map(static function (InvoiceRecipient $recipient): array {
            return self::normalizeRecipient($recipient);
        }, $invoice->getRecipients());
        $lines = array_map(function (InvoiceLine $line): array {
            return $this->normalizeLine($line);
        }, $invoice->getLines());

        $header = [];
        /* @noinspection SpellCheckingInspection */
        $header['sum1:ObligadoEmision'] = $this->normalizeIssuer($invoice);
        if ($invoice->getRepresentativeId()) {
            $header['sum1:Representante'] = $this->normalizeRepresentative($invoice);
        }

        /* @noinspection SpellCheckingInspection */
        $record = [
            'sum1:IDVersion' => '1.0',
            'sum1:IDFactura' => [
                'sum1:IDEmisorFactura' => $invoice->getIssuerId(),
                'sum1:NumSerieFactura' => $invoice->getNumber(),
                'sum1:FechaExpedicionFactura' => $invoice->getDate()->format('d-m-Y'),
            ],
            'sum1:NombreRazonEmisor' => $invoice->getIssuerName(),
            'sum1:TipoFactura' => $invoice->getType(),
        ];

        if (InvoiceType::isCorrective($invoice->getType())) {
            $corrected = array_map(static function (Invoice $correctedInvoice): array {
                return self::normalizeCorrectedInvoice($correctedInvoice);
            }, $invoice->getCorrectedInvoices());
            $record['sum1:TipoRectificativa'] = $invoice->getCorrectiveType();
            $record['sum1:FacturasRectificadas'] = [
                'sum1:IDFacturaRectificada' => $corrected,
            ];
        }

        /* @noinspection SpellCheckingInspection */
        $record = array_merge($record, [
            'sum1:DescripcionOperacion' => $invoice->getDescription(),
            'sum1:Destinatarios' => [
                'sum1:IDDestinatario' => $recipients,
            ],
            'sum1:Desglose' => [
                'sum1:DetalleDesglose' => $lines,
            ],
            'sum1:CuotaTotal' => $this->numberFormatter->format($invoice->getTotalTaxAmount()),
            'sum1:ImporteTotal' => $this->numberFormatter->format($invoice->getTotalAmount()),
            'sum1:Encadenamiento' => $this->normalizePreviousInvoice($invoice),
            'sum1:SistemaInformatico' => $this->normalizeComputerSystem(),
            'sum1:FechaHoraHusoGenRegistro' => $invoice->getHashedAt()->format('c'),
            'sum1:TipoHuella' => '01',
            'sum1:Huella' => $invoice->getHash(),
        ]);

        /* @noinspection SpellCheckingInspection */
        return [
            'sum:RegFactuSistemaFacturacion' => [
                'sum:Cabecera' => $header,
                'sum:RegistroFactura' => [
                    'sum1:RegistroAlta' => $record,
                ],
            ],
        ];
    }

    private function normalizeIssuer(Invoice $invoice): array
    {
        /* @noinspection SpellCheckingInspection */
        return [
            'sum1:NombreRazon' => $invoice->getIssuerName(),
            'sum1:NIF' => $invoice->getIssuerId(),
        ];
    }

    private function normalizeRepresentative(Invoice $invoice): array
    {
        /* @noinspection SpellCheckingInspection */
        return [
            'sum1:NombreRazon' => $invoice->getRepresentativeName(),
            'sum1:NIF' => $invoice->getRepresentativeId(),
        ];
    }

    private function normalizePreviousInvoice(Invoice $invoice): array
    {
        if (!$invoice->getPreviousInvoice()) {
            return [
                'sum1:PrimerRegistro' => 'S',
            ];
        }

        $date = $invoice->getPreviousDate() ? $invoice->getPreviousDate()->format('d-m-Y') : '';

        /* @noinspection SpellCheckingInspection */
        return [
            'sum1:RegistroAnterior' => [
                'sum1:IDEmisorFactura' => $invoice->getPreviousIssuerId(),
                'sum1:NumSerieFactura' => $invoice->getPreviousNumber(),
                'sum1:FechaExpedicionFactura' => $date,
                'sum1:Huella' => $invoice->getPreviousHash(),
            ],
        ];
    }

    private function normalizeComputerSystem(): array
    {
        /* @noinspection SpellCheckingInspection */
        return [
            'sum1:NombreRazon' => $this->computerSystem->getVendorName(),
            'sum1:NIF' => $this->computerSystem->getVendorId(),
            'sum1:NombreSistemaInformatico' => $this->computerSystem->getName(),
            'sum1:IdSistemaInformatico' => $this->computerSystem->getId(),
            'sum1:Version' => $this->computerSystem->getVersion(),
            'sum1:NumeroInstalacion' => $this->computerSystem->getInstallationNumber(),
            'sum1:TipoUsoPosibleSoloVerifactu' => $this->computerSystem->getOnlySupportsVerifactu() ? 'S' : 'N',
            'sum1:TipoUsoPosibleMultiOT' => $this->computerSystem->getSupportsMultiplePayers() ? 'S' : 'N',
            'sum1:IndicadorMultiplesOT' => $this->computerSystem->getHasMultipleTaxpayers() ? 'S' : 'N',
        ];
    }

    private static function normalizeRecipient(InvoiceRecipient $recipient): array
    {
        if ($recipient->isLocal()) {
            /* @noinspection SpellCheckingInspection */
            return [
                'sum1:NombreRazon' => $recipient->getName(),
                'sum1:NIF' => $recipient->getRecipientId(),
            ];
        }

        /* @noinspection SpellCheckingInspection */
        return [
            'sum1:NombreRazon' => $recipient->getName(),
            'sum1:IDOtro' => [
                'sum1:CodigoPais' => $recipient->getCountry(),
                'sum1:IDType' => $recipient->getIdType(),
                'sum1:ID' => $recipient->getRecipientId(),
            ],
        ];
    }

    private function normalizeLine(InvoiceLine $line): array
    {
        /* @noinspection SpellCheckingInspection */
        return [
            'sum1:Impuesto' => $line->getTaxType(),
            'sum1:ClaveRegimen' => $line->getRegimeType(),
            'sum1:CalificacionOperacion' => $line->getOperationType(),
            'sum1:TipoImpositivo' => $this->numberFormatter->format($line->getTaxRate()),
            'sum1:BaseImponibleOimporteNoSujeto' => $this->numberFormatter->format($line->getBaseAmount()),
            'sum1:CuotaRepercutida' => $this->numberFormatter->format($line->getTaxAmount()),
        ];
    }

    private static function normalizeCorrectedInvoice(Invoice $correctedInvoice): array
    {
        /* @noinspection SpellCheckingInspection */
        return [
            'sum1:IDEmisorFactura' => $correctedInvoice->getIssuerId(),
            'sum1:NumSerieFactura' => $correctedInvoice->getNumber(),
            'sum1:FechaExpedicionFactura' => $correctedInvoice->getDate()->format('d-m-Y'),
        ];
    }
}

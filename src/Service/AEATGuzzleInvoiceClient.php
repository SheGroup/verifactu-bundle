<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Model\Certificate;
use SheGroup\VerifactuBundle\Model\ComputerSystem;
use SheGroup\VerifactuBundle\Model\Response;
use Throwable;

final class AEATGuzzleInvoiceClient implements InvoiceClient
{
    private InvoiceXmlGenerator $xmlGenerator;
    private ResponseParser $responseParser;
    private ComputerSystem $computerSystem;
    private Certificate $certificate;

    public function __construct(
        InvoiceXmlGenerator $xmlGenerator,
        ResponseParser $responseParser,
        ComputerSystem $computerSystem,
        Certificate $certificate
    ) {
        $this->xmlGenerator = $xmlGenerator;
        $this->responseParser = $responseParser;
        $this->computerSystem = $computerSystem;
        $this->certificate = $certificate;
    }

    public function sendInvoice(Invoice $invoice): Response
    {
        try {
            /* @noinspection SpellCheckingInspection */
            $httpResponse = $this->createClient()->post('/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP', [
                RequestOptions::BODY => $this->xmlGenerator->generate($invoice),
            ]);
        } catch (Throwable $e) {
            return new Response('Error', null, $e->getCode(), $e->getMessage());
        }

        return $this->responseParser->parse($httpResponse->getBody()->getContents());
    }

    private function createClient(): Client
    {
        return new Client([
            'base_uri' => $this->computerSystem->getApiDomain(),
            'curl' => [
                CURLOPT_SSLCERT => $this->certificate->getPath(),
                CURLOPT_SSLCERTTYPE => 'P12',
                CURLOPT_SSLCERTPASSWD => $this->certificate->getPassword(),
            ],
            RequestOptions::HEADERS => [
                'Content-Type' => 'text/xml',
                'User-Agent' => sprintf(
                    'Mozilla/5.0 (compatible; %s/%s)',
                    $this->computerSystem->getName(),
                    $this->computerSystem->getVersion()
                ),
            ],
        ]);
    }
}

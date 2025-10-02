<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use DOMDocument;
use DOMXPath;
use SheGroup\VerifactuBundle\Model\Response;

final class AEATResponseParser implements ResponseParser
{
    public function parse(string $xmlString): Response
    {
        $doc = new DOMDocument();
        $doc->loadXML($xmlString);

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('env', 'http://schemas.xmlsoap.org/soap/envelope/');
        /* @noinspection SpellCheckingInspection */
        $xpath->registerNamespace(
            'tikR',
            sprintf(
                '%s/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/RespuestaSuministro.xsd',
                'https://www2.agenciatributaria.gob.es'
            )
        );
        /* @noinspection SpellCheckingInspection */
        $xpath->registerNamespace(
            'tik',
            sprintf(
                '%s/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd',
                'https://www2.agenciatributaria.gob.es'
            )
        );

        if ($xpath->query('//env:Fault')->length > 0) {
            $errorNodes = $xpath->query('//faultstring');
            /* @noinspection SpellCheckingInspection */
            $errorMessage = $errorNodes->length > 0
                ? trim((string) $errorNodes->item(0)->nodeValue)
                : 'Codigo[0].Desconocido';
            $success = preg_match('/Codigo\[(\d+)]\s*\.\s*(.+)/siu', $errorMessage, $matches);

            return $success
                ? new Response('Error', null, (int) $matches[1], trim($matches[2]))
                : new Response('Error', null, 0, $errorMessage);
        }

        $statusNodes = $xpath->query('//tikR:EstadoRegistro');
        $csvNodes = $xpath->query('//tikR:CSV');
        $errorNodes = $xpath->query('//tikR:CodigoErrorRegistro');
        $errorMessageNodes = $xpath->query('//tikR:DescripcionErrorRegistro');

        return new Response(
            $statusNodes->length > 0 ? trim((string) $statusNodes->item(0)->nodeValue) : 'Desconocido',
            $csvNodes->length > 0 ? trim((string) $csvNodes->item(0)->nodeValue) : null,
            $errorNodes->length > 0 ? (int) $errorNodes->item(0)->nodeValue : null,
            $errorMessageNodes->length > 0 ? trim((string) $errorMessageNodes->item(0)->nodeValue) : null
        );
    }
}

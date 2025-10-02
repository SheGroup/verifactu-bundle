<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Controller;

use RuntimeException;
use SheGroup\VerifactuBundle\Model\Certificate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/** @Route("/cert/check/", name="she_group.verifactu.cert.check", methods={"GET"}) */
final class CertificateController extends Controller
{
    private Certificate $certificate;

    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /** @throws Throwable */
    public function __invoke(): Response
    {
        if (!$this->certificate->isEnabled()) {
            return new JsonResponse(['status' => 'disabled'], 404);
        }

        try {
            $expiration = $this->certificate->getCertificateExpiration();
        } catch (RuntimeException $exception) {
            return new JsonResponse(['status' => 'error'], 500);
        }

        $status = 'ok';
        $code = 200;
        if ($this->certificate->expiresSoon()) {
            $status = 'expires soon';
            $code = 403;
        }
        if ($this->certificate->isExpired()) {
            $status = 'expired';
            $code = 500;
        }

        return new JsonResponse([
            'status' => $status,
            'expiration' => $expiration->format('Y-m-d'),
        ], $code);
    }
}

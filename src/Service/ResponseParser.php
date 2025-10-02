<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Model\Response;

interface ResponseParser
{
    public function parse(string $xmlString): Response;
}

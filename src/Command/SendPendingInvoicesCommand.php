<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Command;

use SheGroup\VerifactuBundle\Exception\CannotProcessInvoiceException;
use SheGroup\VerifactuBundle\Repository\InvoiceRepository;
use SheGroup\VerifactuBundle\Service\InvoiceProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SendPendingInvoicesCommand extends Command
{
    protected static $defaultName = 'verifactu:send-pending-invoices';

    private InvoiceRepository $invoiceRepository;
    private InvoiceProcessor $manager;

    public function __construct(InvoiceRepository $invoiceRepository, InvoiceProcessor $manager)
    {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->manager = $manager;
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $invoices = $this->invoiceRepository->getPendingToSend($limit);

        foreach ($invoices as $invoice) {
            try {
                $response = $this->manager->process($invoice);
            } catch (CannotProcessInvoiceException $exception) {
                $response = $exception->getResponse();
            }
            if ($response->isSuccess()) {
                continue;
            }

            $output->writeln(
                sprintf(
                    '<error>Error processing invoice %s: %s (%s)</error>',
                    $invoice->getNumber(),
                    $response->getErrorMessage(),
                    $response->getErrorCode()
                )
            );

            return 1;
        }

        $output->writeln(sprintf('<info>%s invoices sent.</info>', count($invoices)));

        return 0;
    }
}

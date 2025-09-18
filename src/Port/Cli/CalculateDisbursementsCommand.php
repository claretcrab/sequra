<?php

namespace App\Port\Cli;

use App\Domain\MerchantRepository;
use App\Domain\OrderRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:calculate-disbursements')]
class CalculateDisbursementsCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly MerchantRepository $merchantRepository,
    ) {
        parent::__construct();
    }

    public function __invoke(OutputInterface $output): int
    {
        $merchantReferences = $this->orderRepository->findMerchantsWithoutDisbursement();

        foreach ($merchantReferences as $merchantReference) {
            $merchant = $this->merchantRepository->findByReference($merchantReference);

            if ($merchant === null) {
                $output->writeln('<error>Merchant not found: ' . $merchantReference . '</error>');
                continue;
            }

            var_dump($merchant->disbursementFrequency()->value);
        }

        return Command::SUCCESS;
    }
}

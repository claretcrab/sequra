<?php

namespace App\Port\Cli;

use App\Application\DisbursementCalculator;
use App\Domain\OrderRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:calculate-disbursements')]
class CalculateDisbursementsCommand extends Command
{
    public function __construct(
        private readonly OrderRepository    $orderRepository,
        private readonly DisbursementCalculator $disbursementCalculator,
    )
    {
        parent::__construct();
    }

    public function __invoke(#[Argument('Day(s) offset.')] int $offset, OutputInterface $output): int
    {
        $today = new \DateTimeImmutable('now');
        $calculationDate = $today->modify('-' . $offset . ' days');

        do {
            $output->writeln('<info>Calculating disbursements for date: ' . $calculationDate->format('Y-m-d') . '</info>');

            $merchantReferences = $this->orderRepository->findMerchantsWithoutDisbursement($calculationDate);

            foreach ($merchantReferences as $merchantReference) {
                $this->disbursementCalculator->calculate($calculationDate, $merchantReference);
            }

            $calculationDate = $calculationDate->modify('+1 day');
        } while ($calculationDate < $today);

        return Command::SUCCESS;
    }
}

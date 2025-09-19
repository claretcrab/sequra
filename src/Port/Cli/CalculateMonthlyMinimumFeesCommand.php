<?php

namespace App\Port\Cli;

use App\Application\MonthlyMinimumFeeCalculator;
use App\Domain\BusinessConstants;
use App\Domain\DisbursementRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:calculate-monthly-minimum-fees')]
class CalculateMonthlyMinimumFeesCommand extends Command
{
    public function __construct(
        private readonly DisbursementRepository $disbursementRepository,
        private readonly MonthlyMinimumFeeCalculator $monthlyMinimumFeeCalculator,
    ) {
        parent::__construct();
    }

    public function __invoke(#[Argument('Month(s) offset.')] int $offset, OutputInterface $output): int
    {
        $today = new \DateTimeImmutable('now');
        $calculationDate = $today->modify('-'.$offset.' months');

        do {
            $output->writeln('<info>Calculating monthly minimum fees for date: '.$calculationDate->format(BusinessConstants::DATE_FORMAT).'</info>');

            $disbursements = $this->disbursementRepository->getMonthlyStatistics($calculationDate);

            foreach ($disbursements as $disbursement) {
                $this->monthlyMinimumFeeCalculator->calculate($calculationDate, $disbursement['merchant_reference'], $disbursement['total_fee']);
            }

            $calculationDate = $calculationDate->modify('+1 month');
        } while ($calculationDate < $today);

        return Command::SUCCESS;
    }
}

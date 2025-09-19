<?php

namespace App\Port\Cli;

use App\Domain\BusinessConstants;
use App\Domain\DisbursementRepository;
use App\Domain\MonthlyMinimumFeeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:generate-reports')]
class GenerateReportsCommand extends Command
{
    public function __construct(
        private readonly DisbursementRepository $disbursementRepository,
        private readonly MonthlyMinimumFeeRepository $monthlyMinimumFeeRepository,
    ) {
        parent::__construct();
    }

    public function __invoke(OutputInterface $output): int
    {
        $disbursementStatistics = $this->disbursementRepository->getStatistics();
        $monthlyFeeStatistics = $this->monthlyMinimumFeeRepository->getStatistics();

        $fmt = new \NumberFormatter(BusinessConstants::LOCALE, \NumberFormatter::CURRENCY);
        $table = new Table($output);
        $table
            ->setHeaders(['Year', 'Number of disbursements', 'Amount disbursed to merchants', 'Amount of order fees'])
        ;

        $row = 0;
        foreach ($disbursementStatistics as $statistic) {
            $year = new \DateTimeImmutable($statistic['year']);
            $table->setRow($row, [
                $year->format('Y'),
                $statistic['total_number'],
                $fmt->formatCurrency($statistic['total_amount'] / 100, BusinessConstants::CURRENCY),
                $fmt->formatCurrency($statistic['total_fee'] / 100, BusinessConstants::CURRENCY),
            ]);
            ++$row;
        }

        $table->render();

        $table = new Table($output);
        $table
            ->setHeaders(['Year', 'Number of monthly fees charged', 'Amount of monthly fees charged'])
        ;

        $row = 0;
        foreach ($monthlyFeeStatistics as $statistic) {
            $year = new \DateTimeImmutable($statistic['year']);
            $table->setRow($row, [
                $year->format('Y'),
                $statistic['total_number'],
                $fmt->formatCurrency($statistic['total_fee'] / 100, BusinessConstants::CURRENCY),
            ]);
            ++$row;
        }

        $table->render();

        return Command::SUCCESS;
    }
}

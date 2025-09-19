<?php

namespace App\Port\Cli;

use App\Domain\DisbursementFrequency;
use App\Domain\DisbursementRepository;
use App\Domain\Merchant;
use App\Domain\MerchantRepository;
use App\Domain\OrderRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:generate-report')]
class GenerateReportCommand extends Command
{
    public function __construct(
        private readonly DisbursementRepository $disbursementRepository,
    ) {
        parent::__construct();
    }

    public function __invoke(OutputInterface $output): int
    {

        $statistics = $this->disbursementRepository->getStatistics();


        $table = new Table($output);
        $table
            ->setHeaders(['Year', 'Number of disbursements', 'Amount disbursed to merchants', 'Amount of order fees', 'Number of monthly fees charged', 'Amount of monthly fees charged'])
        ;

        $row = 0;
        foreach ($statistics as $statistic) {
            $year = new \DateTimeImmutable(($statistic['year']));
            $table->setRow($row, [
                $year->format('Y'),
                $statistic['total_number'],
                $statistic['total_amount'] / 100 . ' €',
                $statistic['total_fee'] / 100 . ' €',
                '',
                '',
            ]);
            $row++;
        }

        $table->render();

        return Command::SUCCESS;
    }
}

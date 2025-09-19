<?php

namespace App\Port\Cli;

use App\Domain\DisbursementFrequency;
use App\Domain\DisbursementRepository;
use App\Domain\Merchant;
use App\Domain\MerchantRepository;
use App\Domain\OrderRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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



        $output->writeln('<info>Report generated.</info>');
        return Command::SUCCESS;
    }
}

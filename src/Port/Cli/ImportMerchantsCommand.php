<?php

namespace App\Port\Cli;

use App\Domain\DisbursementFrequency;
use App\Domain\Merchant;
use App\Domain\MerchantRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:import-merchants')]
class ImportMerchantsCommand extends Command
{
    public function __construct(
        private readonly MerchantRepository $merchantRepository,
    ) {
        parent::__construct();
    }

    public function __invoke(OutputInterface $output): int
    {
        $filePath = 'csv/merchants.csv';

        if (!file_exists($filePath)) {
            $output->writeln('<error>CSV file not found: '.$filePath.'</error>');

            return Command::FAILURE;
        }

        $file = fopen($filePath, 'r');
        if (false === $file) {
            $output->writeln('<error>Failed to open CSV file: '.$filePath.'</error>');

            return Command::FAILURE;
        }

        // Skip the header row
        fgetcsv($file);

        while (($data = fgetcsv($file, null, ';')) !== false) {
            try {
                $merchant = new Merchant(
                    id: Uuid::fromString($data[0]),
                    reference: $data[1],
                    email: $data[2],
                    liveOn: new \DateTimeImmutable($data[3]),
                    disbursementFrequency: DisbursementFrequency::from($data[4]),
                    minimumMonthlyFee: (int) ($data[5] * 100), // Convert to cents
                );

                $this->merchantRepository->save($merchant);
                $output->writeln('<info>Imported merchant: '.$data[1].'</info>');
            } catch (\Exception $e) {
                $output->writeln('<error>Failed to import merchant: '.$e->getMessage().'</error>');
            }
        }

        fclose($file);

        $output->writeln('<info>All merchants have been imported.</info>');

        return Command::SUCCESS;
    }
}

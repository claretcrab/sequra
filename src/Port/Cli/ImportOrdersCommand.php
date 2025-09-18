<?php

namespace App\Port\Cli;

use App\Domain\Order;
use App\Domain\OrderRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-orders')]
class ImportOrdersCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {
        parent::__construct();
    }

    public function __invoke(OutputInterface $output): int
    {
        $filePath = 'csv/orders.csv';

        if (!file_exists($filePath)) {
            $output->writeln('<error>CSV file not found: ' . $filePath . '</error>');
            return Command::FAILURE;
        }

        $file = fopen($filePath, 'r');
        if ($file === false) {
            $output->writeln('<error>Failed to open CSV file: ' . $filePath . '</error>');
            return Command::FAILURE;
        }

        // Skip the header row
        fgetcsv($file);

        while (($data = fgetcsv($file, null, ';')) !== false) {
            try {
                $order = new Order(
                    id: $data[0],
                    merchantReference: $data[1],
                    amount: (int)$data[2] * 100, // Convert to cents,
                    createdAt: new \DateTimeImmutable($data[3]),
                );

                $this->orderRepository->save($order);
                $output->writeln('<info>Imported order: ' . $data[1] . '</info>');
            } catch (\Exception $e) {
                $output->writeln('<error>Failed to import order: ' . $e->getMessage() . '</error>');
            }
        }

        fclose($file);

        $output->writeln('<info>All orders have been imported.</info>');
        return Command::SUCCESS;
    }
}

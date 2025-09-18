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
        set_time_limit(0);

        $filePath = 'csv/orders.csv';

        if (!file_exists($filePath)) {
            $output->writeln('<error>CSV file not found: ' . $filePath . '</error>');
            return Command::FAILURE;
        }


        $row = 0;
        foreach ($this->readCsv($filePath) as $data) {
            try {
                $order = new Order(
                    id: $data[0],
                    merchantReference: $data[1],
                    amount: (int)$data[2] * 100, // Convert to cents,
                    createdAt: new \DateTimeImmutable($data[3]),
                );

                $this->orderRepository->save($order);
                unset($order);
                $output->writeln('<info>Imported order: ' . $data[1] . '</info>');
                unset($data);
                $row++;
                if($row % 100 === 0) {
                    var_dump("Processed $row orders");
                    gc_collect_cycles();
                }
            } catch (\Exception $e) {
                $output->writeln('<error>Failed to import order: ' . $e->getMessage() . '</error>');
            }
        }

        $output->writeln('<info>All orders have been imported.</info>');
        return Command::SUCCESS;
    }

    private function readCsv(string $filePath): \Generator
    {
        $file = fopen($filePath, 'r');
        if ($file === false) {
            throw new \RuntimeException('Failed to open CSV file: ' . $filePath);
        }

        // Skip the header row
        fgetcsv($file);

        while (($data = fgetcsv($file, null, ';')) !== false) {
            yield $data;
        }

        fclose($file);
    }
}

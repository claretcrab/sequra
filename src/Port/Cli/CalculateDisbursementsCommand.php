<?php

namespace App\Port\Cli;

use App\Domain\Disbursement;
use App\Domain\DisbursementFrequency;
use App\Domain\DisbursementRepository;
use App\Domain\MerchantRepository;
use App\Domain\OrderRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:calculate-disbursements')]
class CalculateDisbursementsCommand extends Command
{
    public function __construct(
        private readonly OrderRepository    $orderRepository,
        private readonly MerchantRepository $merchantRepository,
        private readonly DisbursementRepository $disbursementRepository,
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
                $merchant = $this->merchantRepository->findByReference($merchantReference);

                if ($merchant === null) {
                    $output->writeln('<error>Merchant not found: ' . $merchantReference . '</error>');
                    continue;
                }

                if ($merchant->disbursementFrequency() === DisbursementFrequency::WEEKLY &&
                    $merchant->liveOn()->format('N') !== $calculationDate->format('N')) {
                    $output->writeln('<error>Merchant not elegible: ' . $merchantReference . '</error>');
                    continue;
                }

                $orderAggregate = $this->orderRepository->findOrdersWithoutDisbursementByMerchant(
                    merchantReference: $merchantReference,
                    createdAt: $calculationDate,
                );

                $disbursementId = Uuid::v7();

                $disbursement = new Disbursement(
                    id: $disbursementId,
                    amount: $orderAggregate['total_amount'],
                    fee: $orderAggregate['total_fee'],
                    merchantReference: $merchantReference,
                    disbursedAt: $calculationDate,
                );
                //TODO: transaction
                $this->disbursementRepository->save($disbursement);

                $this->orderRepository->markOrdersAsDisbursed(
                    merchantReference: $merchantReference,
                    createdAt: $calculationDate,
                    disbursementId: $disbursementId,
                );
            }

            $calculationDate = $calculationDate->modify('+1 day');
        } while ($calculationDate < $today);

        return Command::SUCCESS;
    }
}

<?php

namespace App\Port\Cli;

use App\Domain\Disbursement;
use App\Domain\DisbursementFrequency;
use App\Domain\DisbursementRepository;
use App\Domain\DisbursementStatus;
use App\Domain\MerchantRepository;
use App\Domain\OrderRepository;
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

    public function __invoke(OutputInterface $output): int
    {
        $merchantReferences = $this->orderRepository->findMerchantsWithoutDisbursement();

        foreach ($merchantReferences as $merchantReference) {
            $merchant = $this->merchantRepository->findByReference($merchantReference);

            if ($merchant === null) {
                $output->writeln('<error>Merchant not found: ' . $merchantReference . '</error>');
                continue;
            }

            $orders = $this->orderRepository->findOrdersWithoutDisbursementByMerchant(
                merchantReference: $merchantReference,
            );

            $ordersToDisburse = [];
            foreach ($orders as $order) {
                $ordersToDisburse[$order->createdAt()->format('Y-m-d')][] = $order;
            }

            foreach ($ordersToDisburse as $date => $ordersGroup) {
                if ($merchant->disbursementFrequency() === DisbursementFrequency::DAILY) {
                    $disbursementId = Uuid::v7();

                    $totalAmount = 0;
                    $totalFee = 0;
                    foreach ($ordersGroup as $order) {
                        $order->setDisbursementStatus(DisbursementStatus::DISBURSED);
                        $order->setDisbursementId($disbursementId);
                        $this->orderRepository->update($order);
                        $totalAmount += $order->amount();
                        if ($totalAmount < 5000) {
                            $fee = (int)round($order->amount() * 0.01);
                        } elseif ($totalAmount <= 30000) {
                            $fee = (int)round($order->amount() * 0.0095);
                        } else {
                            $fee = (int)round($order->amount() * 0.0085);
                        }
                        $totalFee += $fee;
                    }
                    $disbursement = new Disbursement(
                        id: $disbursementId,
                        amount: $totalAmount,
                        fee: $totalFee,
                    );
                    $this->disbursementRepository->save($disbursement);
                    $output->writeln(
                        sprintf(
                            'Disbursed %d orders for merchant %s on %s',
                            count($ordersGroup),
                            $merchantReference,
                            $date,
                        ),
                    );
                    exit;
//                } elseif ($merchant->disbursementFrequency() === DisbursementFrequency::WEEKLY) {
//                    $dayOfWeek = (new \DateTime($date))->format('N');
//                    if ($dayOfWeek == 7) { // Sunday
//                        foreach ($ordersGroup as $order) {
//                            $order->setDisbursementStatus(DisbursementStatus::PENDING);
//                            $this->orderRepository->save($order);
//                        }
//                        $output->writeln(
//                            sprintf(
//                                'Disbursed %d orders for merchant %s on %s',
//                                count($ordersGroup),
//                                $merchantReference,
//                                $date,
//                            ),
//                        );
//                    }
//                }
                }
            }
        }
        return Command::SUCCESS;
    }
}

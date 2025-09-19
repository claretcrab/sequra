<?php

namespace App\Tests\Port\Repository;

use App\Domain\DisbursementStatus;
use App\Domain\Order;
use App\Domain\OrderRepository;
use App\Port\Repository\DbalOrderRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DbalOrderRepositoryTest extends KernelTestCase
{
    private Connection $connection;
    private OrderRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->connection = $kernel->getContainer()->get('doctrine.dbal.default_connection');
        $this->repository = $kernel->getContainer()->get(DbalOrderRepository::class);
        $this->connection->beginTransaction();
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
        $this->connection->close();
    }

    public function testSaveAndFind(): void
    {
        $order = new Order(
            id: '056d024481a9',
            merchantReference: 'treutel_schumm_fadel',
            amount: 6174,
            createdAt: new \DateTimeImmutable('2023-01-01'),
            disbursementStatus: DisbursementStatus::PENDING,
        );

        $this->repository->save($order);

        $fetchedOrder = $this->repository->findById('056d024481a9');
        $this->assertNotNull($fetchedOrder);
    }

    public function testNotFound(): void
    {
        $fetchedOrder = $this->repository->findById('000000000000');
        $this->assertNull($fetchedOrder);
    }
}

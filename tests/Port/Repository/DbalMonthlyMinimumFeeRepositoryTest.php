<?php

namespace App\Tests\Port\Repository;

use App\Domain\MonthlyMinimumFee;
use App\Domain\MonthlyMinimumFeeRepository;
use App\Port\Repository\DbalMonthlyMinimumFeeRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class DbalMonthlyMinimumFeeRepositoryTest extends KernelTestCase
{
    private Connection $connection;
    private MonthlyMinimumFeeRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->connection = $kernel->getContainer()->get('doctrine.dbal.default_connection');
        $this->repository = $kernel->getContainer()->get(DbalMonthlyMinimumFeeRepository::class);
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
        $this->expectNotToPerformAssertions();

        $disbursement = new MonthlyMinimumFee(
            id: new Uuid('2ae89f6d-e210-4993-b4d1-0bd2d279da62'),
            merchantReference: 'treutel_schumm_fadel',
            fee: 100,
            createdAt: new \DateTimeImmutable('2022-01-01'),
        );

        $this->repository->save($disbursement);
    }
}

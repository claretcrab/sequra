<?php

namespace App\Tests\Port\Repository;

use App\Domain\Disbursement;
use App\Domain\DisbursementRepository;
use App\Port\Repository\DbalDisbursementRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class DbalDisbursementRepositoryTest extends KernelTestCase
{
    private Connection $connection;
    private DisbursementRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->connection = $kernel->getContainer()->get('doctrine.dbal.default_connection');
        $this->repository = $kernel->getContainer()->get(DbalDisbursementRepository::class);
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
        $disbursement = new Disbursement(
            id: new Uuid('2ae89f6d-e210-4993-b4d1-0bd2d279da62'),
            amount: 9900,
            fee: 100,
            merchantReference: 'treutel_schumm_fadel',
            disbursedAt: new \DateTimeImmutable('2022-01-01'),
        );

        $this->repository->save($disbursement);

        $fetchedDisbursement = $this->repository->findById(new Uuid('2ae89f6d-e210-4993-b4d1-0bd2d279da62'));
        $this->assertNotNull($fetchedDisbursement);
    }

    public function testNotFound(): void
    {
        $fetchedDisbursement = $this->repository->findById(new Uuid('00000000-0000-0000-0000-000000000000'));
        $this->assertNull($fetchedDisbursement);
    }
}

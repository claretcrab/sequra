<?php

namespace App\Tests\Port\Repository;

use App\Domain\DisbursementFrequency;
use App\Domain\Merchant;
use App\Domain\MerchantRepository;
use App\Port\Repository\DbalMerchantRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class DbalMerchantRepositoryTest extends KernelTestCase
{
    private Connection $connection;
    private MerchantRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->connection = $kernel->getContainer()->get('doctrine.dbal.default_connection');
        $this->repository = $kernel->getContainer()->get(DbalMerchantRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->connection->close();
    }

    public function testSaveAndFind(): void
    {
        $merchant = new Merchant(
            id: new Uuid('2ae89f6d-e210-4993-b4d1-0bd2d279da62'),
            reference: 'treutel_schumm_fadel',
            email: 'info@treutel-schumm-and-fadel.com',
            liveOn: new \DateTimeImmutable('2022-01-01'),
            disbursementFrequency: DisbursementFrequency::WEEKLY,
            minimumMonthlyFee: 2900
        );

        $this->repository->save($merchant);

        $fetchedMerchant = $this->repository->findById(new Uuid('2ae89f6d-e210-4993-b4d1-0bd2d279da62'));
        $this->assertNotNull($fetchedMerchant);
    }

    public function testNotFound(): void
    {
        $fetchedMerchant = $this->repository->findById(new Uuid('00000000-0000-0000-0000-000000000000'));
        $this->assertNull($fetchedMerchant);
    }
}

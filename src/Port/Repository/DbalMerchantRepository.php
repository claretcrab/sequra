<?php

namespace App\Port\Repository;

use App\Domain\BusinessConstants;
use App\Domain\DisbursementFrequency;
use App\Domain\Merchant;
use App\Domain\MerchantRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Uuid;

class DbalMerchantRepository implements MerchantRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function save(Merchant $merchant): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert('merchants')
            ->values([
                'id' => ':id',
                'reference' => ':reference',
                'email' => ':email',
                'live_on' => ':live_on',
                'disbursement_frequency' => ':disbursement_frequency',
                'minimum_monthly_fee' => ':minimum_monthly_fee',
            ])
            ->setParameter('id', $merchant->id()->toString())
            ->setParameter('reference', $merchant->reference())
            ->setParameter('email', $merchant->email())
            ->setParameter('live_on', $merchant->liveOn()->format(BusinessConstants::DATE_FORMAT))
            ->setParameter('disbursement_frequency', $merchant->disbursementFrequency()->value)
            ->setParameter('minimum_monthly_fee', $merchant->minimumMonthlyFee());
        $qb->executeStatement();
    }

    public function findById(Uuid $id): ?Merchant
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from('merchants')
            ->where('id = :id')
            ->setParameter('id', $id->toString());

        $result = $qb->executeQuery()->fetchAssociative();

        if (!$result) {
            return null;
        }

        return new Merchant(
            Uuid::fromString($result['id']),
            $result['reference'],
            $result['email'],
            new \DateTimeImmutable($result['live_on']),
            DisbursementFrequency::from($result['disbursement_frequency']),
            (int) $result['minimum_monthly_fee']
        );
    }

    public function findByReference(string $reference): ?Merchant
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from('merchants')
            ->where('reference = :reference')
            ->setParameter('reference', $reference);

        $result = $qb->executeQuery()->fetchAssociative();

        if (!$result) {
            return null;
        }

        return new Merchant(
            Uuid::fromString($result['id']),
            $result['reference'],
            $result['email'],
            new \DateTimeImmutable($result['live_on']),
            DisbursementFrequency::from($result['disbursement_frequency']),
            (int) $result['minimum_monthly_fee']
        );
    }
}

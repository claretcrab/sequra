<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250919083911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
ALTER TABLE monthly_minimum_fees
ADD CONSTRAINT unique_merchant_reference_created_at UNIQUE (merchant_reference, created_at);
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
ALTER TABLE monthly_minimum_fees
DROP CONSTRAINT unique_merchant_reference_created_at;
SQL;
        $this->addSql($sql);
    }
}

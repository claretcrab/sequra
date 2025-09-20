<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250918155621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create disbursements table and add disbursement_id to orders table';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE TABLE disbursements (
    id CHAR(36) NOT NULL PRIMARY KEY, -- UUID stored as a string
    amount BIGINT NOT NULL,
    fee BIGINT NOT NULL,
    merchant_reference VARCHAR(255) NOT NULL,
    disbursed_at DATE NOT NULL
);
SQL;

        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE orders ADD disbursement_id CHAR(36) DEFAULT NULL;
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
DROP TABLE disbursements;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE orders DROP disbursement_id;
SQL;

        $this->addSql($sql);
    }

}

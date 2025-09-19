<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250918134325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create orders table and disbursement_status enum';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE TYPE disbursement_status AS ENUM('PENDING', 'DISBURSED');
SQL;

        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE orders (
    id CHAR(12) NOT NULL PRIMARY KEY,
    merchant_reference VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    created_at DATE NOT NULL,
    disbursement_status disbursement_status NOT NULL,
    fee INT NOT NULL
);
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
DROP TABLE orders;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
DROP TYPE disbursement_status;
SQL;
        $this->addSql($sql);
    }
}

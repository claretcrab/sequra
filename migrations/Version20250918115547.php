<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250918115547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create merchants table and disbursement_frequency enum';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE TYPE disbursement_frequency AS ENUM('DAILY', 'WEEKLY');
SQL;

        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE merchants (
    id CHAR(36) NOT NULL PRIMARY KEY, -- UUID stored as a string
    reference VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    live_on DATE NOT NULL,
    disbursement_frequency disbursement_frequency NOT NULL,
    minimum_monthly_fee INT NOT NULL
);
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
DROP TABLE merchants;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
DROP TYPE disbursement_frequency;
SQL;
        $this->addSql($sql);
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250919073654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create monthly_minimum_fees table';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE TABLE monthly_minimum_fees (
    id CHAR(36) NOT NULL PRIMARY KEY, -- UUID stored as a string
    merchant_reference VARCHAR(255) NOT NULL,
    fee INT NOT NULL,
    created_at DATE NOT NULL
);
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
DROP TABLE monthly_minimum_fees;
SQL;
        $this->addSql($sql);
    }
}

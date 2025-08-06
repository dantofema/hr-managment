<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250806000530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE employees (
        id VARCHAR(36) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        department VARCHAR(50) NOT NULL,
        role VARCHAR(50) NOT NULL,
        status VARCHAR(20) NOT NULL,
        PRIMARY KEY(id)
    )');
        $this->addSql('CREATE UNIQUE INDEX uniq_ba82c300e7927c74 ON employees (email)');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS employees');
        $this->addSql('DROP INDEX IF EXISTS uniq_ba82c300e7927c74 ON employees');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250809141604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_payrolls_employee_id RENAME TO IDX_694037328C03F15C');
        $this->addSql('ALTER TABLE users ALTER is_active DROP DEFAULT');
        $this->addSql('ALTER INDEX idx_e3eaac178c03f15c RENAME TO IDX_3B8290678C03F15C');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER INDEX idx_694037328c03f15c RENAME TO idx_payrolls_employee_id');
        $this->addSql('ALTER TABLE users ALTER is_active SET DEFAULT true');
        $this->addSql('ALTER INDEX idx_3b8290678c03f15c RENAME TO idx_e3eaac178c03f15c');
    }
}

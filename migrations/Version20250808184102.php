<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250808184102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vacations (id VARCHAR(36) NOT NULL, employee_id VARCHAR(36) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, reason TEXT NOT NULL, status VARCHAR(20) NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rejection_reason TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E3EAAC178C03F15C ON vacations (employee_id)');
        $this->addSql('COMMENT ON COLUMN vacations.start_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN vacations.end_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN vacations.approved_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN vacations.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN vacations.updated_at IS \'(DC2Type:datetime_immutable)\'');
        
        // Add foreign key constraints
        $this->addSql('ALTER TABLE vacations ADD CONSTRAINT FK_E3EAAC178C03F15C FOREIGN KEY (employee_id) REFERENCES employees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payrolls ADD CONSTRAINT FK_payrolls_employee_id FOREIGN KEY (employee_id) REFERENCES employees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_payrolls_employee_id ON payrolls (employee_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        
        // Drop foreign key constraints and indexes
        $this->addSql('ALTER TABLE vacations DROP CONSTRAINT FK_E3EAAC178C03F15C');
        $this->addSql('ALTER TABLE payrolls DROP CONSTRAINT FK_payrolls_employee_id');
        $this->addSql('DROP INDEX IDX_E3EAAC178C03F15C');
        $this->addSql('DROP INDEX IDX_payrolls_employee_id');
        
        // Drop vacations table
        $this->addSql('DROP TABLE vacations');
    }
}

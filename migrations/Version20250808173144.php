<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250808173144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employees ALTER hired_at TYPE DATE');
        $this->addSql('ALTER TABLE employees RENAME COLUMN salary TO salary_amount');
        $this->addSql('ALTER TABLE employees RENAME COLUMN currency TO salary_currency');
        $this->addSql('COMMENT ON COLUMN employees.hired_at IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE payrolls ADD start_date DATE NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD end_date DATE NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD gross_salary_amount NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD taxes_amount NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD social_security_amount NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD health_insurance_amount NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD net_salary_amount NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD net_salary_currency VARCHAR(3) NOT NULL');
        $this->addSql('ALTER TABLE payrolls DROP period_start_date');
        $this->addSql('ALTER TABLE payrolls DROP period_end_date');
        $this->addSql('ALTER TABLE payrolls DROP gross_salary');
        $this->addSql('ALTER TABLE payrolls DROP taxes');
        $this->addSql('ALTER TABLE payrolls DROP social_security');
        $this->addSql('ALTER TABLE payrolls DROP health_insurance');
        $this->addSql('ALTER TABLE payrolls DROP other_deductions');
        $this->addSql('ALTER TABLE payrolls DROP net_salary');
        $this->addSql('ALTER TABLE payrolls RENAME COLUMN currency TO gross_salary_currency');
        $this->addSql('COMMENT ON COLUMN payrolls.start_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN payrolls.end_date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE employees ALTER hired_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE employees RENAME COLUMN salary_amount TO salary');
        $this->addSql('ALTER TABLE employees RENAME COLUMN salary_currency TO currency');
        $this->addSql('COMMENT ON COLUMN employees.hired_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE payrolls ADD period_start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD period_end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD gross_salary NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD taxes NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD social_security NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD health_insurance NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD other_deductions NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD net_salary NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payrolls ADD currency VARCHAR(3) NOT NULL');
        $this->addSql('ALTER TABLE payrolls DROP start_date');
        $this->addSql('ALTER TABLE payrolls DROP end_date');
        $this->addSql('ALTER TABLE payrolls DROP gross_salary_amount');
        $this->addSql('ALTER TABLE payrolls DROP gross_salary_currency');
        $this->addSql('ALTER TABLE payrolls DROP taxes_amount');
        $this->addSql('ALTER TABLE payrolls DROP social_security_amount');
        $this->addSql('ALTER TABLE payrolls DROP health_insurance_amount');
        $this->addSql('ALTER TABLE payrolls DROP net_salary_amount');
        $this->addSql('ALTER TABLE payrolls DROP net_salary_currency');
        $this->addSql('COMMENT ON COLUMN payrolls.period_start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN payrolls.period_end_date IS \'(DC2Type:datetime_immutable)\'');
    }
}

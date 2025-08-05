<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250805220214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create salaries and payrolls tables for payroll system';
    }

    public function up(Schema $schema): void
    {
        // Create salaries table
        $this->addSql('CREATE TABLE salaries (
            employee_id VARCHAR(36) NOT NULL PRIMARY KEY,
            base_salary NUMERIC(10, 2) NOT NULL,
            bonus NUMERIC(10, 2) NOT NULL DEFAULT 0,
            currency VARCHAR(3) NOT NULL DEFAULT \'USD\',
            role VARCHAR(50) NOT NULL,
            effective_date TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL,
            CONSTRAINT fk_salaries_employee_id FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE
        )');

        // Create payrolls table
        $this->addSql('CREATE TABLE payrolls (
            id VARCHAR(36) NOT NULL PRIMARY KEY,
            employee_id VARCHAR(36) NOT NULL,
            gross_salary NUMERIC(10, 2) NOT NULL,
            income_tax NUMERIC(10, 2) NOT NULL,
            social_security NUMERIC(10, 2) NOT NULL,
            health_insurance NUMERIC(10, 2) NOT NULL,
            total_deductions NUMERIC(10, 2) NOT NULL,
            net_salary NUMERIC(10, 2) NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT \'USD\',
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            calculated_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL,
            CONSTRAINT fk_payrolls_employee_id FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE
        )');

        // Create indexes for better performance
        $this->addSql('CREATE INDEX idx_payrolls_employee_id ON payrolls (employee_id)');
        $this->addSql('CREATE INDEX idx_payrolls_period ON payrolls (period_start, period_end)');
        $this->addSql('CREATE UNIQUE INDEX idx_payrolls_employee_period ON payrolls (employee_id, period_start, period_end)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS payrolls');
        $this->addSql('DROP TABLE IF EXISTS salaries');
    }
}

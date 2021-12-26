<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211226175728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE readable_identifier ADD tenant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE readable_identifier ADD CONSTRAINT FK_15CF59E9033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_15CF59E9033212A ON readable_identifier (tenant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE readable_identifier DROP CONSTRAINT FK_15CF59E9033212A');
        $this->addSql('DROP INDEX IDX_15CF59E9033212A');
        $this->addSql('ALTER TABLE readable_identifier DROP tenant_id');
    }
}

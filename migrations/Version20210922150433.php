<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210922150433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE boulder_label DROP CONSTRAINT fk_a89d620333b92f39');
        $this->addSql('DROP SEQUENCE label_id_seq CASCADE');
        $this->addSql('DROP TABLE boulder_label');
        $this->addSql('DROP TABLE migration_versions');
        $this->addSql('DROP TABLE label');
        $this->addSql('ALTER TABLE area ADD active BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('ALTER TABLE grade ALTER active SET DEFAULT \'true\'');
        $this->addSql('ALTER TABLE hold_color ALTER active SET DEFAULT \'true\'');
        $this->addSql('ALTER TABLE notification ALTER active SET DEFAULT \'true\'');
        $this->addSql('ALTER TABLE tag ADD active BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('ALTER TABLE users ALTER active SET DEFAULT \'true\'');
        $this->addSql('ALTER TABLE wall ALTER active SET DEFAULT \'true\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE label_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE boulder_label (boulder_id INT NOT NULL, label_id INT NOT NULL, PRIMARY KEY(boulder_id, label_id))');
        $this->addSql('CREATE INDEX idx_a89d620387658a6f ON boulder_label (boulder_id)');
        $this->addSql('CREATE INDEX idx_a89d620333b92f39 ON boulder_label (label_id)');
        $this->addSql('CREATE TABLE migration_versions (version VARCHAR(14) NOT NULL, executed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(version))');
        $this->addSql('COMMENT ON COLUMN migration_versions.executed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE label (id INT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_ea750e8a76ed395 ON label (user_id)');
        $this->addSql('ALTER TABLE boulder_label ADD CONSTRAINT fk_a89d620333b92f39 FOREIGN KEY (label_id) REFERENCES label (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_label ADD CONSTRAINT fk_a89d620387658a6f FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE label ADD CONSTRAINT fk_ea750e8a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE grade ALTER active SET DEFAULT \'false\'');
        $this->addSql('ALTER TABLE hold_color ALTER active SET DEFAULT \'false\'');
        $this->addSql('ALTER TABLE area DROP active');
        $this->addSql('ALTER TABLE notification ALTER active SET DEFAULT \'false\'');
        $this->addSql('ALTER TABLE tag DROP active');
        $this->addSql('ALTER TABLE users ALTER active DROP DEFAULT');
        $this->addSql('ALTER TABLE wall ALTER active SET DEFAULT \'false\'');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211205093421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD visible BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E925CFF21E FOREIGN KEY (last_visited_location) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1483A5E925CFF21E ON users (last_visited_location)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E925CFF21E');
        $this->addSql('DROP INDEX IDX_1483A5E925CFF21E');
        $this->addSql('ALTER TABLE event DROP visible');
    }
}

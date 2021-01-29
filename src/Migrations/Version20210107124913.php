<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210107124913 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE boulder_setters');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE boulder_setters (boulder_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(boulder_id, user_id))');
        $this->addSql('CREATE INDEX idx_1f9e1541a76ed395 ON boulder_setters (user_id)');
        $this->addSql('CREATE INDEX idx_1f9e154187658a6f ON boulder_setters (boulder_id)');
        $this->addSql('ALTER TABLE boulder_setters ADD CONSTRAINT fk_1f9e154187658a6f FOREIGN KEY (boulder_id) REFERENCES boulder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_setters ADD CONSTRAINT fk_1f9e1541a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

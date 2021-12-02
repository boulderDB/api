<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211202182246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE readable_identifier_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE event_user (event_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(event_id, user_id))');
        $this->addSql('CREATE INDEX IDX_92589AE271F7E88B ON event_user (event_id)');
        $this->addSql('CREATE INDEX IDX_92589AE2A76ED395 ON event_user (user_id)');
        $this->addSql('CREATE TABLE readable_identifier (id INT NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder ADD readable_identifier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE boulder ADD CONSTRAINT FK_D17AF4379ADA58EC FOREIGN KEY (readable_identifier_id) REFERENCES readable_identifier (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D17AF4379ADA58EC ON boulder (readable_identifier_id)');
        $this->addSql('ALTER TABLE event ADD public BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE boulder DROP CONSTRAINT FK_D17AF4379ADA58EC');
        $this->addSql('DROP SEQUENCE readable_identifier_id_seq CASCADE');
        $this->addSql('DROP TABLE event_user');
        $this->addSql('DROP TABLE readable_identifier');
        $this->addSql('DROP INDEX UNIQ_D17AF4379ADA58EC');
        $this->addSql('ALTER TABLE boulder DROP readable_identifier_id');
        $this->addSql('ALTER TABLE event DROP public');
    }
}

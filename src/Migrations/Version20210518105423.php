<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210518105423 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE boulder_rating_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE boulder_rating (id INT NOT NULL, author_id INT DEFAULT NULL, boulder_id INT DEFAULT NULL, rating INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EFF727E0F675F31B ON boulder_rating (author_id)');
        $this->addSql('CREATE INDEX IDX_EFF727E087658A6F ON boulder_rating (boulder_id)');
        $this->addSql('ALTER TABLE boulder_rating ADD CONSTRAINT FK_EFF727E0F675F31B FOREIGN KEY (author_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_rating ADD CONSTRAINT FK_EFF727E087658A6F FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
    }
}

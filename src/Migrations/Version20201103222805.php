<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201103222805 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE boulder_label_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE label_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE label (id INT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EA750E8A76ED395 ON label (user_id)');
        $this->addSql('CREATE TABLE boulder_label (boulder_id INT NOT NULL, label_id INT NOT NULL, PRIMARY KEY(boulder_id, label_id))');
        $this->addSql('CREATE INDEX IDX_A89D620387658A6F ON boulder_label (boulder_id)');
        $this->addSql('CREATE INDEX IDX_A89D620333B92F39 ON boulder_label (label_id)');
        $this->addSql('ALTER TABLE label ADD CONSTRAINT FK_EA750E8A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_label ADD CONSTRAINT FK_A89D620387658A6F FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_label ADD CONSTRAINT FK_A89D620333B92F39 FOREIGN KEY (label_id) REFERENCES label (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210513201840 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE boulder_comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE boulder_comment (id INT NOT NULL, author_id INT DEFAULT NULL, boulder_id INT DEFAULT NULL, tenant_id INT DEFAULT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE INDEX IDX_E1298FF1F675F31B ON boulder_comment (author_id)');
        $this->addSql('CREATE INDEX IDX_E1298FF187658A6F ON boulder_comment (boulder_id)');
        $this->addSql('CREATE INDEX IDX_E1298FF19033212A ON boulder_comment (tenant_id)');

        $this->addSql('ALTER TABLE boulder_comment ADD CONSTRAINT FK_E1298FF1F675F31B FOREIGN KEY (author_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_comment ADD CONSTRAINT FK_E1298FF187658A6F FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_comment ADD CONSTRAINT FK_E1298FF19033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE grade ADD active BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE hold_color ADD active BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200613215024 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tenant ADD city VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD zip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD address_line_one VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD address_line_two VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD country_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD website VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD facebook VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD instagram VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD twitter VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tenant DROP city');
        $this->addSql('ALTER TABLE tenant DROP zip');
        $this->addSql('ALTER TABLE tenant DROP address_line_one');
        $this->addSql('ALTER TABLE tenant DROP address_line_two');
        $this->addSql('ALTER TABLE tenant DROP country_code');
        $this->addSql('ALTER TABLE tenant DROP image');
        $this->addSql('ALTER TABLE tenant DROP website');
        $this->addSql('ALTER TABLE tenant DROP facebook');
        $this->addSql('ALTER TABLE tenant DROP instagram');
        $this->addSql('ALTER TABLE tenant DROP twitter');
    }
}

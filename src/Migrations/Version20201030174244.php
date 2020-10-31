<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201030174244 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE circuit_boulder DROP CONSTRAINT fk_eca7a2d2cf2182c8');
        $this->addSql('ALTER TABLE topo_boulders DROP CONSTRAINT fk_9a9aefd9a5c5c2c7');

        $this->addSql('DROP TABLE circuit_boulder');
        $this->addSql('DROP TABLE wall_topo');

        $this->addSql('DROP TABLE circuit');
        $this->addSql('DROP TABLE topo_boulders');

        $this->addSql('CREATE TABLE setter_locations (setter_id INT NOT NULL, location_id INT NOT NULL, PRIMARY KEY(setter_id, location_id))');
        $this->addSql('CREATE TABLE boulder_label (id INT NOT NULL, boulder_id INT DEFAULT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}

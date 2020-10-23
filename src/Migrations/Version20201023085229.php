<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201023085229 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE timeslot ADD min_quantity INT DEFAULT 1 NOT NULL;");
        $this->addSql("ALTER TABLE timeslot RENAME COLUMN allow_quantity TO max_quantity;");
        $this->addSql("ALTER TABLE timeslot ALTER COLUMN max_quantity SET DEFAULT 1;");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

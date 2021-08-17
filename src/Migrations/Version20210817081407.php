<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210817081407 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE boulder_doubt DROP CONSTRAINT fk_3d76e59536f63d6e');
        $this->addSql('DROP INDEX idx_3d76e59536f63d6e');
        $this->addSql('ALTER TABLE boulder_doubt DROP ascent_id');
        $this->addSql('DROP INDEX uniq_7c825e44a76ed395');
        $this->addSql('DROP INDEX uniq_7c825e44f85e0677');
        $this->addSql('CREATE INDEX IDX_7C825E44A76ED395 ON setter (user_id)');
    }

    public function down(Schema $schema) : void
    {
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211225124625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ALTER roles TYPE TEXT');
        $this->addSql('ALTER TABLE notification ALTER roles DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN notification.roles IS \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE notification ALTER roles TYPE TEXT');
        $this->addSql('ALTER TABLE notification ALTER roles DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN notification.roles IS \'(DC2Type:array)\'');
    }
}

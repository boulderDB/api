<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\Version;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201222220142 extends AbstractMigration
{

    public function getDescription(): string
    {
        $statement = "SELECT id, media FROM users WHERE media IS NOT NULL";
        $query = $this->connection->prepare($statement);

        $query->execute();
        $results = $query->fetchAll();

        foreach ($results as $result) {
            $media = "https://storage.boulderdb.de/boulderdb-uploads/" . $result["media"];

            $this->addSql("UPDATE users SET media = '{$media}' WHERE ID = {$result["id"]}");
        }

        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

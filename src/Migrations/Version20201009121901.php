<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201009121901 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE topo_boulders DROP CONSTRAINT fk_9a9aefd9a5c5c2c7');
        $this->addSql('ALTER TABLE circuit_boulder DROP CONSTRAINT fk_eca7a2d2cf2182c8');
        $this->addSql('DROP SEQUENCE api_users_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE boulder_comment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE boulder_rating_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE circuit_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE event_ranking_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE feed_item_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE import_transaction_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE permission_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE reset_password_hash_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_ranking_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_tag_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE value_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE wall_topo_id_seq CASCADE');
        $this->addSql('DROP TABLE api_users');
        $this->addSql('DROP TABLE boulder_comment');
        $this->addSql('DROP TABLE circuit_boulder');
        $this->addSql('DROP TABLE boulder_rating');
        $this->addSql('DROP TABLE event_ranking');
        $this->addSql('DROP TABLE import_transaction');
        $this->addSql('DROP TABLE reset_password_hash');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE topo_boulders');
        $this->addSql('DROP TABLE value');
        $this->addSql('DROP TABLE user_ranking');
        $this->addSql('DROP TABLE wall_topo');
        $this->addSql('DROP TABLE user_tenants');
        $this->addSql('DROP TABLE user_tag');
        $this->addSql('DROP TABLE circuit');
        $this->addSql('DROP TABLE feed_item');
        $this->addSql('DROP INDEX "primary"');
        $this->addSql('ALTER TABLE boulder_tags ADD boulder_tag_id INT NOT NULL');
        $this->addSql('ALTER TABLE boulder_tags ADD CONSTRAINT FK_E9D9188387658A6FBAD26311 FOREIGN KEY (boulder_id, tag_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_tags ADD CONSTRAINT FK_E9D918838DAC62D3 FOREIGN KEY (boulder_tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E9D9188387658A6FBAD26311 ON boulder_tags (boulder_id, tag_id)');
        $this->addSql('CREATE INDEX IDX_E9D918838DAC62D3 ON boulder_tags (boulder_tag_id)');
        $this->addSql('ALTER TABLE boulder_tags ADD PRIMARY KEY (boulder_id, tag_id, boulder_tag_id)');
        $this->addSql('DROP INDEX uniq_42c849553f7d58d2');
        $this->addSql('ALTER TABLE tenant ALTER city SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER zip SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER address_line_one SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER address_line_two SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER country_code SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER image SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER website SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER facebook SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER instagram SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER twitter SET NOT NULL');
        $this->addSql('ALTER TABLE wall DROP active');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE api_users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE boulder_comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE boulder_rating_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE circuit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE event_ranking_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE feed_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE import_transaction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE reset_password_hash_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_ranking_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_tag_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE wall_topo_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE api_users (id INT NOT NULL, username VARCHAR(64) NOT NULL, token VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_3016f1395f37a13b ON api_users (token)');
        $this->addSql('CREATE UNIQUE INDEX uniq_3016f139f85e0677 ON api_users (username)');
        $this->addSql('CREATE TABLE boulder_comment (id INT NOT NULL, boulder_id INT DEFAULT NULL, user_id INT DEFAULT NULL, tenant_id INT DEFAULT NULL, text TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_e1298ff1a76ed395 ON boulder_comment (user_id)');
        $this->addSql('CREATE INDEX idx_e1298ff19033212a ON boulder_comment (tenant_id)');
        $this->addSql('CREATE INDEX idx_e1298ff187658a6f ON boulder_comment (boulder_id)');
        $this->addSql('CREATE TABLE circuit_boulder (circuit_id INT NOT NULL, boulder_id INT NOT NULL, PRIMARY KEY(circuit_id, boulder_id))');
        $this->addSql('CREATE INDEX idx_eca7a2d2cf2182c8 ON circuit_boulder (circuit_id)');
        $this->addSql('CREATE INDEX idx_eca7a2d287658a6f ON circuit_boulder (boulder_id)');
        $this->addSql('CREATE TABLE boulder_rating (id INT NOT NULL, boulder_id INT DEFAULT NULL, user_id INT DEFAULT NULL, tenant_id INT DEFAULT NULL, rating VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_eff727e0a76ed395 ON boulder_rating (user_id)');
        $this->addSql('CREATE INDEX idx_eff727e087658a6f ON boulder_rating (boulder_id)');
        $this->addSql('CREATE INDEX idx_eff727e09033212a ON boulder_rating (tenant_id)');
        $this->addSql('CREATE TABLE event_ranking (id INT NOT NULL, event_id INT DEFAULT NULL, user_id INT DEFAULT NULL, tenant_id INT DEFAULT NULL, rank INT DEFAULT NULL, topped INT DEFAULT NULL, flashed INT DEFAULT NULL, resigned INT DEFAULT NULL, points INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_5ef907fa76ed395 ON event_ranking (user_id)');
        $this->addSql('CREATE INDEX idx_5ef907f9033212a ON event_ranking (tenant_id)');
        $this->addSql('CREATE INDEX idx_5ef907f71f7e88b ON event_ranking (event_id)');
        $this->addSql('CREATE TABLE import_transaction (id INT NOT NULL, source_id INT NOT NULL, target_id INT NOT NULL, resource VARCHAR(255) NOT NULL, tenant_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE reset_password_hash (id INT NOT NULL, type VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, "timestamp" TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status INT NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE permission (id INT NOT NULL, tenant_id INT DEFAULT NULL, context VARCHAR(255) NOT NULL, permissions TEXT DEFAULT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_e04992aa9033212a ON permission (tenant_id)');
        $this->addSql('COMMENT ON COLUMN permission.permissions IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE topo_boulders (wall_topo_id INT NOT NULL, boulder_id INT NOT NULL, PRIMARY KEY(wall_topo_id, boulder_id))');
        $this->addSql('CREATE INDEX idx_9a9aefd9a5c5c2c7 ON topo_boulders (wall_topo_id)');
        $this->addSql('CREATE INDEX idx_9a9aefd987658a6f ON topo_boulders (boulder_id)');
        $this->addSql('CREATE TABLE value (id INT NOT NULL, tenant_id INT DEFAULT NULL, key_id VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, resource_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_1d7758349033212a ON value (tenant_id)');
        $this->addSql('CREATE TABLE user_ranking (id INT NOT NULL, user_id INT DEFAULT NULL, tenant_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, rank INT DEFAULT NULL, topped INT DEFAULT NULL, flashed INT DEFAULT NULL, resigned INT DEFAULT NULL, points INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d8b527da9033212a ON user_ranking (tenant_id)');
        $this->addSql('CREATE INDEX idx_d8b527daa76ed395 ON user_ranking (user_id)');
        $this->addSql('CREATE TABLE wall_topo (id INT NOT NULL, wall_id INT DEFAULT NULL, tenant_id INT DEFAULT NULL, data TEXT DEFAULT NULL, media VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_b20d15d4c33923f1 ON wall_topo (wall_id)');
        $this->addSql('CREATE INDEX idx_b20d15d49033212a ON wall_topo (tenant_id)');
        $this->addSql('CREATE TABLE user_tenants (user_id INT NOT NULL, tenant_id INT NOT NULL, PRIMARY KEY(user_id, tenant_id))');
        $this->addSql('CREATE INDEX idx_e0f188b19033212a ON user_tenants (tenant_id)');
        $this->addSql('CREATE INDEX idx_e0f188b1a76ed395 ON user_tenants (user_id)');
        $this->addSql('CREATE TABLE user_tag (id INT NOT NULL, boulder_id INT DEFAULT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, checksum VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_e89fd608de6fdf9a ON user_tag (checksum)');
        $this->addSql('CREATE INDEX idx_e89fd60887658a6f ON user_tag (boulder_id)');
        $this->addSql('CREATE INDEX idx_e89fd608a76ed395 ON user_tag (user_id)');
        $this->addSql('CREATE TABLE circuit (id INT NOT NULL, user_id INT DEFAULT NULL, tenant_id INT DEFAULT NULL, name TEXT NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, public BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_1325f3a6a76ed395 ON circuit (user_id)');
        $this->addSql('CREATE INDEX idx_1325f3a69033212a ON circuit (tenant_id)');
        $this->addSql('CREATE TABLE feed_item (id INT NOT NULL, user_id INT DEFAULT NULL, tenant_id INT DEFAULT NULL, resource_id INT NOT NULL, event VARCHAR(255) NOT NULL, resource_type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_9f8cce49a76ed395 ON feed_item (user_id)');
        $this->addSql('CREATE INDEX idx_9f8cce499033212a ON feed_item (tenant_id)');
        $this->addSql('ALTER TABLE boulder_comment ADD CONSTRAINT fk_e1298ff187658a6f FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_comment ADD CONSTRAINT fk_e1298ff19033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_comment ADD CONSTRAINT fk_e1298ff1a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE circuit_boulder ADD CONSTRAINT fk_eca7a2d287658a6f FOREIGN KEY (boulder_id) REFERENCES boulder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE circuit_boulder ADD CONSTRAINT fk_eca7a2d2cf2182c8 FOREIGN KEY (circuit_id) REFERENCES circuit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_rating ADD CONSTRAINT fk_eff727e087658a6f FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_rating ADD CONSTRAINT fk_eff727e09033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_rating ADD CONSTRAINT fk_eff727e0a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_ranking ADD CONSTRAINT fk_5ef907f71f7e88b FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_ranking ADD CONSTRAINT fk_5ef907f9033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_ranking ADD CONSTRAINT fk_5ef907fa76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT fk_e04992aa9033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE topo_boulders ADD CONSTRAINT fk_9a9aefd987658a6f FOREIGN KEY (boulder_id) REFERENCES boulder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE topo_boulders ADD CONSTRAINT fk_9a9aefd9a5c5c2c7 FOREIGN KEY (wall_topo_id) REFERENCES wall_topo (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE value ADD CONSTRAINT fk_1d7758349033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_ranking ADD CONSTRAINT fk_d8b527da9033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_ranking ADD CONSTRAINT fk_d8b527daa76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wall_topo ADD CONSTRAINT fk_b20d15d49033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wall_topo ADD CONSTRAINT fk_b20d15d4c33923f1 FOREIGN KEY (wall_id) REFERENCES wall (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_tenants ADD CONSTRAINT fk_e0f188b19033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_tenants ADD CONSTRAINT fk_e0f188b1a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_tag ADD CONSTRAINT fk_e89fd60887658a6f FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_tag ADD CONSTRAINT fk_e89fd608a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE circuit ADD CONSTRAINT fk_1325f3a69033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE circuit ADD CONSTRAINT fk_1325f3a6a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feed_item ADD CONSTRAINT fk_9f8cce499033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feed_item ADD CONSTRAINT fk_9f8cce49a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_tags DROP CONSTRAINT FK_E9D9188387658A6FBAD26311');
        $this->addSql('ALTER TABLE boulder_tags DROP CONSTRAINT FK_E9D918838DAC62D3');
        $this->addSql('DROP INDEX IDX_E9D9188387658A6FBAD26311');
        $this->addSql('DROP INDEX IDX_E9D918838DAC62D3');
        $this->addSql('DROP INDEX boulder_tags_pkey');
        $this->addSql('ALTER TABLE boulder_tags DROP boulder_tag_id');
        $this->addSql('ALTER TABLE boulder_tags ADD PRIMARY KEY (boulder_id, tag_id)');
        $this->addSql('ALTER TABLE wall ADD active BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ALTER city DROP NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER zip DROP NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER address_line_one DROP NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER address_line_two DROP NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER country_code DROP NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER image DROP NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER website DROP NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER facebook DROP NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER instagram DROP NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER twitter DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_42c849553f7d58d2 ON reservation (hash_id)');
    }
}

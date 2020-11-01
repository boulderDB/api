<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201101121740 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

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
        $this->addSql('CREATE SEQUENCE boulder_label_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE boulder_setters_v2 (boulder_id INT NOT NULL, setter_id INT NOT NULL, PRIMARY KEY(boulder_id, setter_id))');
        $this->addSql('CREATE INDEX IDX_784980D087658A6F ON boulder_setters_v2 (boulder_id)');
        $this->addSql('CREATE INDEX IDX_784980D0365C7286 ON boulder_setters_v2 (setter_id)');
        $this->addSql('ALTER TABLE boulder_setters_v2 ADD CONSTRAINT FK_784980D087658A6F FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_setters_v2 ADD CONSTRAINT FK_784980D0365C7286 FOREIGN KEY (setter_id) REFERENCES setter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE api_users');
        $this->addSql('DROP TABLE boulder_comment');
        $this->addSql('DROP TABLE boulder_rating');
        $this->addSql('DROP TABLE event_ranking');
        $this->addSql('DROP TABLE import_transaction');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE reset_password_hash');
        $this->addSql('DROP TABLE value');
        $this->addSql('DROP TABLE user_ranking');
        $this->addSql('DROP TABLE user_tenants');
        $this->addSql('DROP TABLE user_tag');
        $this->addSql('DROP TABLE feed_item');
        $this->addSql('ALTER TABLE hold_color DROP media');
        $this->addSql('ALTER TABLE area DROP grade_diversity_target_map');
        $this->addSql('ALTER TABLE timeslot ALTER capacity DROP NOT NULL');
        $this->addSql('ALTER TABLE timeslot ALTER max_quantity SET NOT NULL');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT fk_3bae0aa761220ea6');
        $this->addSql('DROP INDEX idx_3bae0aa761220ea6');
        $this->addSql('ALTER TABLE event DROP creator_id');
        $this->addSql('ALTER TABLE event DROP scoring_order');
        $this->addSql('ALTER TABLE event ALTER visible DROP DEFAULT');
        $this->addSql('ALTER TABLE grade DROP CONSTRAINT fk_595aae346b17ddb7');
        $this->addSql('DROP INDEX idx_595aae346b17ddb7');
        $this->addSql('ALTER TABLE grade DROP external_grade');
        $this->addSql('ALTER TABLE ascent DROP CONSTRAINT fk_781456971f7e88b');
        $this->addSql('DROP INDEX idx_781456971f7e88b');
        $this->addSql('ALTER TABLE ascent DROP event_id');
        $this->addSql('ALTER TABLE ascent ALTER user_id SET NOT NULL');
        $this->addSql('ALTER INDEX idx_7814569a76ed395 RENAME TO "user"');
        $this->addSql('ALTER TABLE users DROP birthday');
        $this->addSql('ALTER TABLE users DROP signature');
        $this->addSql('ALTER TABLE users DROP arm_span');
        $this->addSql('ALTER TABLE users DROP height');
        $this->addSql('ALTER TABLE users DROP created_at');
        $this->addSql('ALTER TABLE users DROP updated_at');
        $this->addSql('ALTER TABLE users DROP migrations');
        $this->addSql('ALTER TABLE users DROP weight');
        $this->addSql('ALTER TABLE users DROP ape_index');
        $this->addSql('ALTER TABLE users ALTER first_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE users ALTER last_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE users ALTER gender TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE setter DROP CONSTRAINT fk_7c825e449033212a');
        $this->addSql('DROP INDEX idx_7c825e44a76ed395');
        $this->addSql('DROP INDEX idx_7c825e449033212a');
        $this->addSql('ALTER TABLE setter DROP tenant_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7C825E44A76ED395 ON setter (user_id)');
        $this->addSql('ALTER TABLE setter_locations ADD CONSTRAINT FK_4D6DEE61365C7286 FOREIGN KEY (setter_id) REFERENCES setter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE setter_locations ADD CONSTRAINT FK_4D6DEE6164D218E FOREIGN KEY (location_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4D6DEE61365C7286 ON setter_locations (setter_id)');
        $this->addSql('CREATE INDEX IDX_4D6DEE6164D218E ON setter_locations (location_id)');
        $this->addSql('DROP INDEX status');
        $this->addSql('CREATE INDEX status ON boulder (status)');
        $this->addSql('ALTER TABLE boulder_tags DROP CONSTRAINT FK_E9D9188387658A6F');
        $this->addSql('ALTER TABLE boulder_tags DROP CONSTRAINT FK_E9D91883BAD26311');
        $this->addSql('ALTER TABLE boulder_tags ADD CONSTRAINT FK_E9D9188387658A6F FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_tags ADD CONSTRAINT FK_E9D91883BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tenant ALTER city SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER zip SET NOT NULL');
        $this->addSql('ALTER TABLE tenant ALTER country_code SET NOT NULL');
        $this->addSql('ALTER TABLE wall DROP active');
        $this->addSql('ALTER TABLE boulder_label ADD CONSTRAINT FK_A89D620387658A6F FOREIGN KEY (boulder_id) REFERENCES boulder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE boulder_label ADD CONSTRAINT FK_A89D6203A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A89D620387658A6F ON boulder_label (boulder_id)');
        $this->addSql('CREATE INDEX IDX_A89D6203A76ED395 ON boulder_label (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}

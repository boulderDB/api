<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211128191639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT fk_42c8495554177093');
        $this->addSql('ALTER TABLE time_slot_exclusion DROP CONSTRAINT fk_19a9b01054177093');
        $this->addSql('ALTER TABLE timeslot DROP CONSTRAINT fk_3be452f754177093');
        $this->addSql('DROP SEQUENCE reservation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE room_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE time_slot_exclusion_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE timeslot_id_seq CASCADE');
        $this->addSql('DROP TABLE event_participant');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE time_slot_exclusion');
        $this->addSql('DROP TABLE timeslot');
        $this->addSql('DROP TABLE room');
        $this->addSql('ALTER TABLE event ADD start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD end_data TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE event DROP description');
        $this->addSql('ALTER TABLE event DROP date_from');
        $this->addSql('ALTER TABLE event DROP date_to');
        $this->addSql('ALTER TABLE event DROP media');
        $this->addSql('ALTER TABLE event DROP scoring_system');
        $this->addSql('ALTER TABLE event DROP created_at');
        $this->addSql('ALTER TABLE event DROP updated_at');
        $this->addSql('ALTER TABLE event DROP public_event');
        $this->addSql('ALTER TABLE event DROP visible');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE reservation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE room_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE time_slot_exclusion_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE timeslot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE event_participant (event_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(event_id, user_id))');
        $this->addSql('CREATE INDEX idx_7c16b891a76ed395 ON event_participant (user_id)');
        $this->addSql('CREATE INDEX idx_7c16b89171f7e88b ON event_participant (event_id)');
        $this->addSql('CREATE TABLE reservation (id INT NOT NULL, user_id INT DEFAULT NULL, room_id INT DEFAULT NULL, hash_id VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, start_time VARCHAR(255) NOT NULL, end_time VARCHAR(255) NOT NULL, appeared BOOLEAN DEFAULT \'false\', guest BOOLEAN DEFAULT \'false\' NOT NULL, email VARCHAR(64) DEFAULT NULL, quantity INT DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, checked_in BOOLEAN DEFAULT \'false\', PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_42c8495554177093 ON reservation (room_id)');
        $this->addSql('CREATE INDEX idx_42c84955a76ed395 ON reservation (user_id)');
        $this->addSql('CREATE TABLE time_slot_exclusion (id INT NOT NULL, room_id INT DEFAULT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, quantity INT DEFAULT NULL, hash_id VARCHAR(255) NOT NULL, note VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_19a9b0103f7d58d2 ON time_slot_exclusion (hash_id)');
        $this->addSql('CREATE INDEX idx_19a9b01054177093 ON time_slot_exclusion (room_id)');
        $this->addSql('CREATE TABLE timeslot (id INT NOT NULL, room_id INT DEFAULT NULL, day_name VARCHAR(255) NOT NULL, start_time VARCHAR(255) NOT NULL, end_time VARCHAR(255) NOT NULL, capacity INT DEFAULT NULL, max_quantity INT DEFAULT 1 NOT NULL, min_quantity INT DEFAULT 1 NOT NULL, enabled BOOLEAN DEFAULT \'true\' NOT NULL, auto_destroy BOOLEAN DEFAULT \'false\' NOT NULL, enable_after TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, disable_after TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_3be452f754177093 ON timeslot (room_id)');
        $this->addSql('CREATE TABLE room (id INT NOT NULL, tenant_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, instructions VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_729f519b9033212a ON room (tenant_id)');
        $this->addSql('ALTER TABLE event_participant ADD CONSTRAINT fk_7c16b89171f7e88b FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_participant ADD CONSTRAINT fk_7c16b891a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT fk_42c8495554177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT fk_42c84955a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE time_slot_exclusion ADD CONSTRAINT fk_19a9b01054177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE timeslot ADD CONSTRAINT fk_3be452f754177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT fk_729f519b9033212a FOREIGN KEY (tenant_id) REFERENCES tenant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD date_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD date_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD media VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD scoring_system VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD public_event BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD visible BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE event DROP start_date');
        $this->addSql('ALTER TABLE event DROP end_data');
    }
}

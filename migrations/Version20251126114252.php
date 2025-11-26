<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126114252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE milestone (id SERIAL NOT NULL, project_id INT DEFAULT NULL, manager_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, planned_start_date DATE DEFAULT NULL, actual_start_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FAC8382166D1F9C ON milestone (project_id)');
        $this->addSql('CREATE INDEX IDX_4FAC8382783E3463 ON milestone (manager_id)');
        $this->addSql('CREATE TABLE project (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN project.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE requirement (id SERIAL NOT NULL, project_id INT DEFAULT NULL, requirement_type_id INT DEFAULT NULL, description TEXT NOT NULL, is_functional BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DB3F5550166D1F9C ON requirement (project_id)');
        $this->addSql('CREATE INDEX IDX_DB3F55509EC6A308 ON requirement (requirement_type_id)');
        $this->addSql('COMMENT ON COLUMN requirement.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE requirement_type (id SERIAL NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE task (id SERIAL NOT NULL, project_id INT DEFAULT NULL, milestone_id INT DEFAULT NULL, manager_id INT DEFAULT NULL, previous_task_id INT DEFAULT NULL, is_functional BOOLEAN NOT NULL, label VARCHAR(255) NOT NULL, invitation_date DATE DEFAULT NULL, planned_start_date DATE DEFAULT NULL, actual_start_date DATE DEFAULT NULL, days_estimate INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_527EDB25166D1F9C ON task (project_id)');
        $this->addSql('CREATE INDEX IDX_527EDB254B3E2EDA ON task (milestone_id)');
        $this->addSql('CREATE INDEX IDX_527EDB25783E3463 ON task (manager_id)');
        $this->addSql('CREATE INDEX IDX_527EDB25BC2D6B55 ON task (previous_task_id)');
        $this->addSql('CREATE TABLE task_requirement (task_id INT NOT NULL, requirement_id INT NOT NULL, PRIMARY KEY(task_id, requirement_id))');
        $this->addSql('CREATE INDEX IDX_9D4AF8628DB60186 ON task_requirement (task_id)');
        $this->addSql('CREATE INDEX IDX_9D4AF8627B576F77 ON task_requirement (requirement_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, project_id INT NOT NULL, name VARCHAR(255) NOT NULL, second_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D93D649166D1F9C ON "user" (project_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE milestone ADD CONSTRAINT FK_4FAC8382166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE milestone ADD CONSTRAINT FK_4FAC8382783E3463 FOREIGN KEY (manager_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE requirement ADD CONSTRAINT FK_DB3F5550166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE requirement ADD CONSTRAINT FK_DB3F55509EC6A308 FOREIGN KEY (requirement_type_id) REFERENCES requirement_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB254B3E2EDA FOREIGN KEY (milestone_id) REFERENCES milestone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25783E3463 FOREIGN KEY (manager_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25BC2D6B55 FOREIGN KEY (previous_task_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task_requirement ADD CONSTRAINT FK_9D4AF8628DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task_requirement ADD CONSTRAINT FK_9D4AF8627B576F77 FOREIGN KEY (requirement_id) REFERENCES requirement (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE milestone DROP CONSTRAINT FK_4FAC8382166D1F9C');
        $this->addSql('ALTER TABLE milestone DROP CONSTRAINT FK_4FAC8382783E3463');
        $this->addSql('ALTER TABLE requirement DROP CONSTRAINT FK_DB3F5550166D1F9C');
        $this->addSql('ALTER TABLE requirement DROP CONSTRAINT FK_DB3F55509EC6A308');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25166D1F9C');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB254B3E2EDA');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25783E3463');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25BC2D6B55');
        $this->addSql('ALTER TABLE task_requirement DROP CONSTRAINT FK_9D4AF8628DB60186');
        $this->addSql('ALTER TABLE task_requirement DROP CONSTRAINT FK_9D4AF8627B576F77');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649166D1F9C');
        $this->addSql('DROP TABLE milestone');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE requirement');
        $this->addSql('DROP TABLE requirement_type');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE task_requirement');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

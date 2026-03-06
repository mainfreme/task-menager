<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306001000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tasks table with assigned user and status';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE tasks (
            id SERIAL NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'To Do' CHECK (status IN ('To Do', 'In Progress', 'Done')),
            assigned_user_id INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )");

        $this->addSql('CREATE INDEX IDX_TASKS_ASSIGNED_USER_ID ON tasks (assigned_user_id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_TASKS_ASSIGNED_USER_ID FOREIGN KEY (assigned_user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('COMMENT ON COLUMN tasks.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN tasks.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tasks');
    }
}


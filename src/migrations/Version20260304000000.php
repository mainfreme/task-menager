<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table with address, geo and company information';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id SERIAL NOT NULL,
            name VARCHAR(255) NOT NULL,
            username VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            address_street VARCHAR(255) DEFAULT NULL,
            address_suite VARCHAR(100) DEFAULT NULL,
            address_city VARCHAR(255) DEFAULT NULL,
            address_zipcode VARCHAR(20) DEFAULT NULL,
            address_geo_lat VARCHAR(50) DEFAULT NULL,
            address_geo_lng VARCHAR(50) DEFAULT NULL,
            phone VARCHAR(15) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            company_name VARCHAR(255) DEFAULT NULL,
            company_catch_phrase VARCHAR(500) DEFAULT NULL,
            company_bs VARCHAR(500) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON users (email)');
        $this->addSql('COMMENT ON COLUMN users.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}

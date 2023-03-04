<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230304225427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__url AS SELECT id, created_at, short_url, accesses, complete_url FROM url');
        $this->addSql('DROP TABLE url');
        $this->addSql('CREATE TABLE url (id VARCHAR(64) NOT NULL, created_at DATE NOT NULL --(DC2Type:date_immutable)
        , short_url VARCHAR(255) NOT NULL, accesses CLOB DEFAULT NULL --(DC2Type:simple_array)
        , complete_url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO url (id, created_at, short_url, accesses, complete_url) SELECT id, created_at, short_url, accesses, complete_url FROM __temp__url');
        $this->addSql('DROP TABLE __temp__url');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__url AS SELECT id, created_at, short_url, accesses, complete_url FROM url');
        $this->addSql('DROP TABLE url');
        $this->addSql('CREATE TABLE url (id BLOB NOT NULL --(DC2Type:ulid)
        , created_at DATE NOT NULL --(DC2Type:date_immutable)
        , short_url VARCHAR(255) NOT NULL, accesses CLOB DEFAULT NULL --(DC2Type:json)
        , complete_url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO url (id, created_at, short_url, accesses, complete_url) SELECT id, created_at, short_url, accesses, complete_url FROM __temp__url');
        $this->addSql('DROP TABLE __temp__url');
    }
}

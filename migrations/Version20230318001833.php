<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230318001833 extends AbstractMigration
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
        $this->addSql('CREATE TABLE url (id VARCHAR(12) NOT NULL, user_id INTEGER DEFAULT NULL, created_at DATE NOT NULL --(DC2Type:date_immutable)
        , short_url VARCHAR(128) NOT NULL, accesses CLOB DEFAULT NULL --(DC2Type:simple_array)
        , complete_url CLOB NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_F47645AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO url (id, created_at, short_url, accesses, complete_url) SELECT id, created_at, short_url, accesses, complete_url FROM __temp__url');
        $this->addSql('DROP TABLE __temp__url');
        $this->addSql('CREATE INDEX IDX_F47645AEA76ED395 ON url (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__url AS SELECT id, created_at, accesses, complete_url, short_url FROM url');
        $this->addSql('DROP TABLE url');
        $this->addSql('CREATE TABLE url (id VARCHAR(12) NOT NULL, created_at DATE NOT NULL --(DC2Type:date_immutable)
        , accesses CLOB DEFAULT NULL --(DC2Type:simple_array)
        , complete_url CLOB NOT NULL, short_url VARCHAR(128) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO url (id, created_at, accesses, complete_url, short_url) SELECT id, created_at, accesses, complete_url, short_url FROM __temp__url');
        $this->addSql('DROP TABLE __temp__url');
    }
}

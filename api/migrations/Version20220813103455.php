<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220813103455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the media object';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media_object (id UUID NOT NULL, file_path VARCHAR(255) DEFAULT NULL, alt_text VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN media_object.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE media_object ADD owner_id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN media_object.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE media_object ADD CONSTRAINT FK_14D431327E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_14D431327E3C61F9 ON media_object (owner_id)');
        $this->addSql('ALTER TABLE media_object ADD original_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE media_object ADD size INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media_object ADD upload_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_14D431327E3C61F9');
        $this->addSql('DROP TABLE media_object');
        $this->addSql('ALTER TABLE media_object DROP upload_time');
    }
}

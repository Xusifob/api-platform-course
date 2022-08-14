<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220814080852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media_object ADD main_object_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE media_object ADD thumbnail_size VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE media_object ADD is_thumbnail BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('COMMENT ON COLUMN media_object.main_object_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE media_object ADD CONSTRAINT FK_14D4313239BF189 FOREIGN KEY (main_object_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_14D4313239BF189 ON media_object (main_object_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE media_object DROP CONSTRAINT FK_14D4313239BF189');
        $this->addSql('DROP INDEX IDX_14D4313239BF189');
        $this->addSql('ALTER TABLE media_object DROP main_object_id');
        $this->addSql('ALTER TABLE media_object DROP thumbnail_size');
        $this->addSql('ALTER TABLE media_object DROP is_thumbnail');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220813113005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a bucket on the media object + Link it to product';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD main_photo_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN product.main_photo_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D34A04ADA7BC5DF9 ON product (main_photo_id)');
        $this->addSql('ALTER TABLE media_object ADD bucket VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE media_object DROP bucket');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADA7BC5DF9');
        $this->addSql('DROP INDEX IDX_D34A04ADA7BC5DF9');
        $this->addSql('ALTER TABLE product DROP main_photo_id');
    }
}

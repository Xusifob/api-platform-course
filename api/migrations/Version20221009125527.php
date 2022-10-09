<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221009125527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media_object DROP CONSTRAINT FK_14D431327E3C61F9');
        $this->addSql('ALTER TABLE media_object ALTER is_thumbnail DROP DEFAULT');
        $this->addSql('ALTER TABLE media_object ADD CONSTRAINT FK_14D431327E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADA7BC5DF9');
        $this->addSql('ALTER TABLE product ALTER price TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE product ALTER reference TYPE VARCHAR(30)');
        $this->addSql('ALTER TABLE product ALTER discount_percent TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES media_object (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_comment DROP CONSTRAINT FK_45AD49DC7E3C61F9');
        $this->addSql('ALTER TABLE product_comment ALTER created_date TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE product_comment ALTER is_moderated DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN product_comment.created_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE product_comment ADD CONSTRAINT FK_45AD49DC7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_comment DROP CONSTRAINT fk_45ad49dc7e3c61f9');
        $this->addSql('ALTER TABLE product_comment ALTER created_date TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE product_comment ALTER is_moderated SET DEFAULT false');
        $this->addSql('COMMENT ON COLUMN product_comment.created_date IS NULL');
        $this->addSql('ALTER TABLE product_comment ADD CONSTRAINT fk_45ad49dc7e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT fk_d34a04ada7bc5df9');
        $this->addSql('ALTER TABLE product ALTER reference TYPE TEXT');
        $this->addSql('ALTER TABLE product ALTER reference TYPE TEXT');
        $this->addSql('ALTER TABLE product ALTER price TYPE SMALLINT');
        $this->addSql('ALTER TABLE product ALTER discount_percent TYPE SMALLINT');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT fk_d34a04ada7bc5df9 FOREIGN KEY (main_photo_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE media_object DROP CONSTRAINT fk_14d431327e3c61f9');
        $this->addSql('ALTER TABLE media_object ALTER is_thumbnail SET DEFAULT false');
        $this->addSql('ALTER TABLE media_object ADD CONSTRAINT fk_14d431327e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

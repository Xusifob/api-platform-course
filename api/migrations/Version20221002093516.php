<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221002093516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product comment';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_comment (id UUID NOT NULL, product_id UUID NOT NULL, owner_id UUID NOT NULL, title VARCHAR(255) NOT NULL, comment TEXT NOT NULL, rating DOUBLE PRECISION NOT NULL, created_date TIMESTAMP(0) WITH TIME ZONE NOT NULL, is_moderated BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_45AD49DC4584665A ON product_comment (product_id)');
        $this->addSql('CREATE INDEX IDX_45AD49DC7E3C61F9 ON product_comment (owner_id)');
        $this->addSql('COMMENT ON COLUMN product_comment.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product_comment.product_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product_comment.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product_comment ADD CONSTRAINT FK_45AD49DC4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_comment ADD CONSTRAINT FK_45AD49DC7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE INDEX idx_75ea56e016ba31db ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0e3bd61ce ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0fb7336f0 ON messenger_messages (queue_name)');
        $this->addSql('ALTER TABLE product_comment DROP CONSTRAINT FK_45AD49DC4584665A');
        $this->addSql('ALTER TABLE product_comment DROP CONSTRAINT FK_45AD49DC7E3C61F9');
        $this->addSql('DROP TABLE product_comment');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Enum\EntityStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220730063255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ads status field in product tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD status SMALLINT NOT NULL default 1');
        $this->addSql('ALTER TABLE product_category ADD status SMALLINT NOT NULL default 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product DROP status');
        $this->addSql('ALTER TABLE product_category DROP status');
    }
}

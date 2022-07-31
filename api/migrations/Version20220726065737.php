<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Product;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220726065737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ads reference and discount percent';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD reference TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD discount_percent SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product DROP reference');
        $this->addSql('ALTER TABLE product DROP discount_percent');
    }


    public function postUp(Schema $schema): void
    {
        $products = $this->connection->fetchAllAssociative("SELECT id FROM product");

        foreach ($products as $product) {
            $reference = Product::generateReference();

            $this->connection->executeQuery("UPDATE product SET reference = :reference WHERE id = :id", [
                'id' => $product['id'],
                'reference' => $reference
            ]);
        }

        $this->connection->executeQuery('ALTER TABLE product ALTER COLUMN reference SET NOT NULL');
        $this->connection->executeQuery('CREATE UNIQUE INDEX UNIQ_D34A04ADAEA34913 ON product (reference)');
    }

}

<?php

namespace App\Tests\Security;


use App\Entity\Enum\EntityStatus;
use App\Entity\Product;
use App\Security\IEntityVoter;
use App\Security\ProductVoter;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ProductVoterTest extends AbstractVoterTest
{

    use ReloadDatabaseTrait;


    /**
     * @param string $username
     * @param string $method
     * @param int $access
     * @return void
     *
     * @dataProvider getViewValues
     */
    public function testView(string $username, string $method, int $access): void
    {
        $address = $this->$method();

        $this->assertVote($username, $address, IEntityVoter::VIEW, $access);
    }


    /**
     * @return array[]
     */
    public function getViewValues(): array
    {
        return [
            "a normal product by a customer" => ["customer1", "loadProduct", VoterInterface::ACCESS_GRANTED],
            "an archived product by a customer" => ["customer1", "loadArchivedProduct", VoterInterface::ACCESS_DENIED],
            "a deleted product by a customer" => ["customer1", "loadDeletedProduct", VoterInterface::ACCESS_DENIED],
            "a normal product by an admin" => ["admin", "loadProduct", VoterInterface::ACCESS_GRANTED],
            "an archived product by an admin" => ["admin", "loadArchivedProduct", VoterInterface::ACCESS_GRANTED],
            "a deleted product by an admin" => ["admin", "loadDeletedProduct", VoterInterface::ACCESS_GRANTED],
        ];
    }

    /**
     * @param string $username
     * @param string $method
     * @param int $access
     * @return void
     *
     * @dataProvider getCreateValues
     */
    public function testCreate(string $username, string $method, int $access): void
    {
        $address = $this->$method();

        $this->assertVote($username, $address, IEntityVoter::CREATE, $access);
    }


    /**
     * @return array[]
     */
    public function getCreateValues(): array
    {
        return [
            "a product by a customer" => ["customer1", "loadProduct", VoterInterface::ACCESS_DENIED],
            "a product by an admin" => ["admin", "loadProduct", VoterInterface::ACCESS_GRANTED],
        ];
    }


    /**
     * @param string $username
     * @param string $method
     * @param int $access
     * @return void
     *
     * @dataProvider getUpdateValues
     */
    public function testUpdate(string $username, string $method, int $access): void
    {
        $address = $this->$method();

        $this->assertVote($username, $address, IEntityVoter::UPDATE, $access);
    }


    /**
     * @return array[]
     */
    public function getUpdateValues(): array
    {
        return [
            "a product by a customer" => ["customer1", "loadProduct", VoterInterface::ACCESS_DENIED],
            "a product by an admin" => ["admin", "loadProduct", VoterInterface::ACCESS_GRANTED],
        ];
    }

    /**
     * @param string $username
     * @param string $method
     * @param int $access
     * @return void
     *
     * @dataProvider getArchiveValues
     */
    public function testArchive(string $username, string $method, int $access): void
    {
        $address = $this->$method();

        $this->assertVote($username, $address, IEntityVoter::ARCHIVE, $access);
    }


    /**
     * @return array[]
     */
    public function getArchiveValues(): array
    {
        return [
            "a product by a customer" => ["customer1", "loadProduct", VoterInterface::ACCESS_DENIED],
            "a product by an admin" => ["admin", "loadProduct", VoterInterface::ACCESS_GRANTED],
            "an archived product by an admin" => ["admin", "loadArchivedProduct", VoterInterface::ACCESS_DENIED],
        ];
    }


    /**
     * @param string $username
     * @param string $method
     * @param int $access
     * @return void
     *
     * @dataProvider getDisArchiveValues
     */
    public function testDisArchive(string $username, string $method, int $access): void
    {
        $address = $this->$method();

        $this->assertVote($username, $address, IEntityVoter::DISARCHIVE, $access);
    }


    /**
     * @return array[]
     */
    public function getDisArchiveValues(): array
    {
        return [
            "a product by a customer" => ["customer1", "loadProduct", VoterInterface::ACCESS_DENIED],
            "a product by an admin" => ["admin", "loadProduct", VoterInterface::ACCESS_DENIED],
            "an archived product by an admin" => ["admin", "loadArchivedProduct", VoterInterface::ACCESS_GRANTED],
        ];
    }


    /**
     * @param string $username
     * @param string $method
     * @param int $access
     * @return void
     *
     * @dataProvider getDeleteValues
     */
    public function testDelete(string $username, string $method, int $access): void
    {
        $address = $this->$method();

        $this->assertVote($username, $address, IEntityVoter::DELETE, $access);
    }


    /**
     * @return array[]
     */
    public function getDeleteValues(): array
    {
        return [
            "a product by a customer" => ["customer1", "loadProduct", VoterInterface::ACCESS_DENIED],
            "a product by an admin" => ["admin", "loadProduct", VoterInterface::ACCESS_GRANTED],
            "a deleted product by an admin" => ["admin", "loadDeletedProduct", VoterInterface::ACCESS_DENIED],
        ];
    }


    public function loadProduct(): Product
    {
        return $this->em->getRepository(Product::class)->findOneBy(['status' => EntityStatus::ACTIVE]);
    }


    public function loadArchivedProduct(): Product
    {
        return $this->em->getRepository(Product::class)->findOneBy(['status' => EntityStatus::ARCHIVED]);
    }

    public function loadDeletedProduct(): Product
    {
        return $this->em->getRepository(Product::class)->findOneBy(['status' => EntityStatus::DELETED]);
    }


    public function getVoter(): string
    {
        return ProductVoter::class;
    }

}

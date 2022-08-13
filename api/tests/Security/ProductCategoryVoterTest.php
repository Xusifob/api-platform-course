<?php

namespace App\Tests\Security;


use App\Entity\Enum\EntityStatus;
use App\Entity\ProductCategory;
use App\Security\IEntityVoter;
use App\Security\ProductCategoryVoter;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ProductCategoryVoterTest extends AbstractVoterTest
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
        $category = $this->$method();

        $this->assertVote($username, $category, IEntityVoter::VIEW, $access);
    }


    /**
     * @return array[]
     */
    public function getViewValues(): array
    {
        return [
            "a normal product category by a customer" => [
                "customer1",
                "loadProductCategory",
                VoterInterface::ACCESS_GRANTED
            ],
            "an archived product category by a customer" => [
                "customer1",
                "loadArchivedProductCategory",
                VoterInterface::ACCESS_DENIED
            ],
            "a deleted product category by a customer" => [
                "customer1",
                "loadDeletedProductCategory",
                VoterInterface::ACCESS_DENIED
            ],
            "a normal product category by an admin" => ["admin", "loadProductCategory", VoterInterface::ACCESS_GRANTED],
            "an archived product category by an admin" => [
                "admin",
                "loadArchivedProductCategory",
                VoterInterface::ACCESS_GRANTED
            ],
            "a deleted product category by an admin" => [
                "admin",
                "loadDeletedProductCategory",
                VoterInterface::ACCESS_GRANTED
            ],
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
        $category = $this->$method();

        $this->assertVote($username, $category, IEntityVoter::CREATE, $access);
    }


    /**
     * @return array[]
     */
    public function getCreateValues(): array
    {
        return [
            "a product category by a customer" => ["customer1", "loadProductCategory", VoterInterface::ACCESS_DENIED],
            "a product category by an admin" => ["admin", "loadProductCategory", VoterInterface::ACCESS_GRANTED],
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
        $category = $this->$method();

        $this->assertVote($username, $category, IEntityVoter::UPDATE, $access);
    }


    /**
     * @return array[]
     */
    public function getUpdateValues(): array
    {
        return [
            "a product category by a customer" => ["customer1", "loadProductCategory", VoterInterface::ACCESS_DENIED],
            "a product category by an admin" => ["admin", "loadProductCategory", VoterInterface::ACCESS_GRANTED],
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
        $category = $this->$method();

        $this->assertVote($username, $category, IEntityVoter::ARCHIVE, $access);
    }


    /**
     * @return array[]
     */
    public function getArchiveValues(): array
    {
        return [
            "a product category by a customer" => ["customer1", "loadProductCategory", VoterInterface::ACCESS_DENIED],
            "a product category by an admin" => ["admin", "loadProductCategory", VoterInterface::ACCESS_GRANTED],
            "an archived product category by an admin" => [
                "admin",
                "loadArchivedProductCategory",
                VoterInterface::ACCESS_DENIED
            ],
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
        $category = $this->$method();

        $this->assertVote($username, $category, IEntityVoter::DISARCHIVE, $access);
    }


    /**
     * @return array[]
     */
    public function getDisArchiveValues(): array
    {
        return [
            "a product category by a customer" => ["customer1", "loadProductCategory", VoterInterface::ACCESS_DENIED],
            "a product category by an admin" => ["admin", "loadProductCategory", VoterInterface::ACCESS_DENIED],
            "an archived product category by an admin" => [
                "admin",
                "loadArchivedProductCategory",
                VoterInterface::ACCESS_GRANTED
            ],
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
        $category = $this->$method();

        $this->assertVote($username, $category, IEntityVoter::DELETE, $access);
    }


    /**
     * @return array[]
     */
    public function getDeleteValues(): array
    {
        return [
            "a product category by a customer" => ["customer1", "loadProductCategory", VoterInterface::ACCESS_DENIED],
            "a product category with a product by an admin" => [
                "admin",
                "loadProductCategory",
                VoterInterface::ACCESS_DENIED
            ],
            "a product category by an admin" => [
                "admin",
                "loadProductCategoryWithoutProduct",
                VoterInterface::ACCESS_GRANTED
            ],
            "a deleted product category by an admin" => [
                "admin",
                "loadDeletedProductCategory",
                VoterInterface::ACCESS_DENIED
            ],
        ];
    }


    public function loadProductCategory(): ProductCategory
    {
        return $this->em->getRepository(ProductCategory::class)->findOneBy(['status' => EntityStatus::ACTIVE]);
    }

    public function loadProductCategoryWithoutProduct(): ProductCategory
    {
        $category = new ProductCategory();
        $category->name = "To delete";
        $category->description = "To delete";
        $this->em->persist($category);
        $this->em->flush();
        return $category;
    }

    public function loadArchivedProductCategory(): ProductCategory
    {
        return $this->em->getRepository(ProductCategory::class)->findOneBy(['status' => EntityStatus::ARCHIVED]);
    }

    public function loadDeletedProductCategory(): ProductCategory
    {
        return $this->em->getRepository(ProductCategory::class)->findOneBy(['status' => EntityStatus::DELETED]);
    }


    public function getVoter(): string
    {
        return ProductCategoryVoter::class;
    }

}

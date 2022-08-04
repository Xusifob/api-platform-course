<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Trait\StatusTrait;
use App\Filter\StatusEntityFilter;
use App\Repository\ProductRepository;
use App\State\Product\ProductProvider;
use App\Validator\IsReference;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('PUBLIC_ACCESS')"),
        new Post(securityPostDenormalize: "is_granted('CREATE',object)"),
        new Put(security: "is_granted('UPDATE',object)"),
        new Delete(security: "is_granted('DELETE',object)")
    ])]
#[ApiResource(
    uriTemplate: '/products/{id}',
    operations: [new Get(security: "is_granted('VIEW',object)")],
    provider: ProductProvider::class
)]
#[ORM\Table(name: "product")]
#[UniqueEntity(fields: "reference")]
#[ApiFilter(StatusEntityFilter::class, properties: ['archived'])]
class Product extends Entity implements IStatusEntity, INamedEntity
{

    use StatusTrait;

    #[Groups(["product:write", "read"])]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[ApiProperty(iris: "https://schema.org/name")]
    #[Assert\NotBlank(message: "product.name.not_blank")]
    #[ApiFilter(SearchFilter::class, strategy: "start")]
    public ?string $name = null;

    #[Groups(["product:item"])]
    #[ORM\Column(type: 'text', nullable: false)]
    #[ApiProperty(iris: "https://schema.org/description")]
    #[Assert\NotBlank(message: "product.description.not_blank")]
    public ?string $description = null;

    #[Groups(["product:item"])]
    #[ORM\Column(type: 'text', unique: true, nullable: false)]
    #[ApiProperty(iris: "https://schema.org/description")]
    #[IsReference(message: "product.reference.invalid")]
    public ?string $reference = null;

    /**
     * @var Collection<int,ProductCategory>
     */
    #[Groups(["product"])]
    #[Link(toProperty: "products")]
    #[ORM\ManyToMany(targetEntity: ProductCategory::class, inversedBy: "products", cascade: ["remove"])]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false)]
    #[Assert\Count(min: 1, minMessage: "product.category.not_null")]
    #[ApiFilter(SearchFilter::class, properties: ['categories.name' => 'exact'])]
    private Collection $categories;

    #[Groups(["product:write", "read"])]
    #[ORM\Column(type: 'smallint', nullable: false)]
    #[ApiProperty(iris: "https://schema.org/price")]
    #[Assert\NotNull(message: "product.price.not_null")]
    #[Assert\Range(minMessage: "product.price.min", min: 0)]
    public ?int $price = null;

    #[Groups(["product"])]
    #[ORM\Column(type: 'smallint', nullable: true)]
    #[ApiProperty(iris: "https://schema.org/Number")]
    #[Assert\Range(notInRangeMessage: "product.price.min", min: 0, max: 100)]
    public ?int $discountPercent = null;


    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->categories = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }


    /**
     * @param ProductCategory $category
     * @return void
     */
    public function addCategory(ProductCategory $category): void
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
    }


    /**
     * @param ProductCategory $category
     * @return void
     */
    public function removeCategory(ProductCategory $category): void
    {
        $this->categories->removeElement($category);
    }


    /**
     * @param Collection $categories
     */
    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }


    #[SerializedName("isSale")]
    #[Groups(["read"])]
    public function isSale(): bool
    {
        return !!$this->discountPercent;
    }


    #[Groups(["read"])]
    public function getSalePrice(): ?int
    {
        if (!$this->isSale()) {
            return null;
        }

        return $this->price * $this->discountPercent;
    }


    public static function generateReference(): string
    {
        return sprintf("P%s", rand(10000, 9999999999));
    }


    public function __toString(): string
    {
        return (string)$this->name;
    }

}

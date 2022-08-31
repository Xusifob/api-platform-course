<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
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
use App\State\Product\ProductProcessor;
use App\State\Product\ProductProvider;
use App\Validator\Enum\MediaType;
use App\Validator\IsMedia;
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
        new GetCollection(
            security: "is_granted('PUBLIC_ACCESS')",
        ),
        new Post(securityPostDenormalize: "is_granted('CREATE',object)", processor: ProductProcessor::class),
        new Put(security: "is_granted('UPDATE',object)", processor: ProductProcessor::class),
        new Delete(security: "is_granted('DELETE',object)")
    ],)]
#[ApiResource(
    uriTemplate: '/products/{id}',
    operations: [new Get(security: "is_granted('VIEW',object)")],
    provider: ProductProvider::class
)]
#[ORM\Table(name: "product")]
#[UniqueEntity(fields: "reference")]
#[ApiFilter(StatusEntityFilter::class, properties: ['archived'])]
#[ApiFilter(RangeFilter::class, properties: ["id"])]
#[ApiFilter(OrderFilter::class, properties: ["id" => "DESC"])]
class Product extends Entity implements IStatusEntity, INamedEntity
{

    use StatusTrait;

    #[Groups(["product:write", "read"])]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[ApiProperty(schema: [
        'type' => 'string',
        'maxLength' => 255,
        'minLength' => 1,
        'example' => 'Product 1',
        'required' => true
    ], iris: "https://schema.org/name")]
    #[Assert\NotBlank(message: "product.name.not_blank")]
    #[ApiFilter(SearchFilter::class, strategy: "start")]
    public ?string $name = null;


    #[Groups(["product:item"])]
    #[ApiProperty(schema: [
        'type' => 'string',
        'example' => 'The description of the product',
        'required' => true
    ], iris: "https://schema.org/description")]
    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotBlank(message: "product.description.not_blank")]
    public ?string $description = null;


    #[Groups(["product:item"])]
    #[ApiProperty(schema: [
        'type' => 'string',
        'example' => 'P123456789',
        'description' => 'The reference of the product. It must follow the format PXXXXXXXXXXX',
        'required' => true
    ], iris: "https://schema.org/identifier")]
    #[ORM\Column(type: 'string', length: 30, unique: true, nullable: false)]
    #[IsReference(message: "product.reference.invalid")]
    public ?string $reference = null;


    /**
     * @var Collection<int,ProductCategory>
     */
    #[Groups(["product"])]
    #[Link(toProperty: "products")]
    #[ApiProperty(schema: [
        'type' => 'array',
        'items' => [
            '$ref' => '#/components/schemas/ProductCategory'
        ]
    ])]
    #[ORM\ManyToMany(targetEntity: ProductCategory::class, inversedBy: "products", cascade: ["remove"])]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false)]
    #[Assert\Count(min: 1, minMessage: "product.category.not_null")]
    #[ApiFilter(SearchFilter::class, properties: ['categories.name' => 'exact'])]
    private Collection $categories;


    #[Groups(["product:write", "read"])]
    #[ORM\Column(type: 'float', nullable: false)]
    #[ApiProperty(schema: [
        'type' => 'float',
        'min' => 0,
        'example' => 49.99,
        'required' => true
    ], iris: "https://schema.org/price")]
    #[Assert\NotNull(message: "product.price.not_null")]
    #[Assert\Range(minMessage: "product.price.min", min: 0)]
    public ?float $price = null;


    #[Groups(["product"])]
    #[ORM\Column(type: 'float', nullable: true)]
    #[ApiProperty(schema: [
        'type' => 'float',
        'min' => 0,
        'max' => 100,
        'example' => 30,
        'required' => true
    ], iris: "https://schema.org/Number")]
    #[Assert\Range(notInRangeMessage: "product.price.min", min: 0, max: 100)]
    public ?float $discountPercent = null;


    #[Groups(["product"])]
    #[ApiProperty(schema: [
        '$ref' => '#/components/schemas/MediaObject'
    ])]
    #[ORM\ManyToOne(targetEntity: MediaObject::class, cascade: ["remove"])]
    #[IsMedia(type: MediaType::IMAGE, message: "product.main_photo.type_invalid")]
    public ?MediaObject $mainPhoto = null;


    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->categories = new ArrayCollection();
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }


    public function addCategory(ProductCategory $category): void
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
    }


    public function removeCategory(ProductCategory $category): void
    {
        $this->categories->removeElement($category);
    }


    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }


    #[SerializedName("isSale")]
    #[Groups(["read"])]
    public function isSale(): bool
    {
        return (bool)$this->discountPercent;
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
        return sprintf("P%s", random_int(10000, 9_999_999_999));
    }


    public function __toString(): string
    {
        return (string)$this->name;
    }

}

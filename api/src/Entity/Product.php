<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Trait\StatusTrait;
use App\Filter\Elasticsearch\StatusEntityFilter as ElasticStatusEntityFilter;
use App\Filter\ProductFilter;
use App\Filter\StatusEntityFilter;
use App\Repository\ProductRepository;
use App\State\ElasticProvider;
use App\State\Product\ProductProcessor;
use App\State\Product\ProductProvider;
use App\Validator\Enum\MediaType;
use App\Validator\IsDiscountValid;
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
            order: [
                "name" => "ASC"
            ],
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(securityPostDenormalize: "is_granted('CREATE',object)", processor: ProductProcessor::class),
        new Put(securityPostDenormalize: "is_granted('UPDATE',previous_object)", processor: ProductProcessor::class),
        new Delete(security: "is_granted('DELETE',object)")
    ],)]
#[ApiResource(
    uriTemplate: '/products/search',
    operations: [new GetCollection(security: "is_granted('PUBLIC_ACCESS')")],
    provider: ElasticProvider::class
)]
#[ApiResource(
    uriTemplate: '/products/{id}',
    operations: [new Get(security: "is_granted('VIEW',object)")],
    provider: ProductProvider::class
)]
#[ApiResource(graphQlOperations: [
    new QueryCollection(security: "is_granted('ROLE_ADMIN')"),
    new Query(security: "is_granted('VIEW',object)", provider: ProductProvider::class),
    new Mutation(security: "is_granted('CREATE',object)", name: 'create', processor: ProductProcessor::class),
])]
#[ApiResource(
    uriTemplate: '/product_categories/{productCategoryId}/products',
    operations: [
        new GetCollection(),
    ],
    uriVariables: [
        'productCategoryId' => new Link(toProperty: 'categories', fromClass: ProductCategory::class),
    ]
)]
#[ORM\Table(name: "product")]
#[UniqueEntity(fields: "reference")]
#[ApiFilter(StatusEntityFilter::class, properties: ['archived'])]
#[ApiFilter(ElasticStatusEntityFilter::class, properties: ['archived'])]
#[ApiFilter(OrderFilter::class, properties: ["id" => "DESC", "reference" => "ASC", "name" => "ASC"])]
#[ApiFilter(ProductFilter::class, properties: ["search"])]
#[IsDiscountValid(message: "product.discount_percent.invalid")]
class Product extends Entity implements IElasticEntity, IStatusEntity, INamedEntity
{

    use StatusTrait;

    #[Groups(["product:write", "read", "elastic"])]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[ApiProperty(schema: [
        'type' => 'string',
        'maxLength' => 255,
        'minLength' => 1,
        'example' => 'Product 1',
        'required' => true
    ], iris: "https://schema.org/name")]
    #[Assert\NotBlank(message: "product.name.not_blank")]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_START)]
    public ?string $name = null;


    #[Groups(["product:item", "elastic"])]
    #[ApiProperty(schema: [
        'type' => 'string',
        'example' => 'The description of the product',
        'required' => true
    ], iris: "https://schema.org/description")]
    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotBlank(message: "product.description.not_blank")]
    public ?string $description = null;


    #[Groups(["product:item", "elastic"])]
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
    #[Groups(["product:read", "product:write"])]
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
    #[ApiFilter(SearchFilter::class, properties: ['categories.name' => SearchFilterInterface::STRATEGY_START])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    private Collection $categories;


    #[Groups(["product:write", "read", "elastic"])]
    #[ORM\Column(type: 'float', nullable: false)]
    #[ApiProperty(schema: [
        'type' => 'float',
        'min' => 0,
        'example' => 49.99,
        'required' => true
    ], iris: "https://schema.org/price")]
    #[Assert\NotNull(message: "product.price.not_null")]
    #[Assert\Range(notInRangeMessage: "product.price.not_in_range", min: 0, max: 10_000_000)]
    public ?float $price = null;


    #[Groups(["product:write", "read", "elastic"])]
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


    #[Groups(["product:read", "product:write"])]
    #[ApiProperty(schema: [
        '$ref' => '#/components/schemas/MediaObject'
    ])]
    #[ORM\ManyToOne(targetEntity: MediaObject::class, cascade: ["remove"])]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true, onDelete: "SET NULL")]
    #[IsMedia(type: MediaType::IMAGE, message: "product.main_photo.type_invalid")]
    public ?MediaObject $mainPhoto = null;


    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->categories = new ArrayCollection();
    }

    #[ApiProperty(security: 'is_granted("VIEW", object)')]
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
    #[Groups(["read", "elastic"])]
    public function isSale(): bool
    {
        return (bool)$this->discountPercent;
    }


    #[Groups(["read", "elastic"])]
    public function getSalePrice(): ?float
    {
        if (!$this->isSale()) {
            return null;
        }

        return $this->price - ($this->price * $this->discountPercent / 100);
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

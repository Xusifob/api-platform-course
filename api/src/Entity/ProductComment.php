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
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Enum\EntityStatus;
use App\Entity\Trait\OwnedTrait;
use App\Entity\Trait\StatusTrait;
use App\Repository\ProductCommentRepository;
use App\Validator\Enum\MediaType;
use App\Validator\IsDiscountValid;
use App\Validator\IsMedia;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProductCommentRepository::class)]
#[ApiResource(
    operations: [
        new Post(securityPostDenormalize: "is_granted('CREATE',object)"),
        new Put(security: "is_granted('UPDATE',previous_object)"),
        new Delete(security: "is_granted('DELETE',object)"),
        new Get(security: "is_granted('VIEW',object)")
    ]
)]
#[ApiResource(
    uriTemplate: '/products/{productId}/comments',
    operations: [
        new GetCollection(
            order: [
                'createdDate' => 'DESC'
            ]
        ),
    ],
    uriVariables: [
        'productId' => new Link(toProperty: 'product', fromClass: Product::class),
    ]
)]
#[ORM\Table(name: "product_comment")]
#[ApiFilter(OrderFilter::class, properties: ["id" => "DESC", "createdDate" => "DESC", "rating" => "ASC"])]
class ProductComment extends Entity implements IElasticEntity, INamedEntity, IOwnedEntity
{

    use OwnedTrait;

    #[Groups(["write", "read"])]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[ApiProperty(schema: [
        'type' => 'string',
        'maxLength' => 255,
        'minLength' => 1,
        'example' => 'Product 1',
        'required' => true
    ], iris: "https://schema.org/name")]
    #[Assert\NotBlank(message: "product_comment.title.not_blank")]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_START)]
    public ?string $title = null;


    #[Groups(["write", "read"])]
    #[ApiProperty(schema: [
        'type' => 'string',
        'example' => 'The description of the product',
        'required' => true
    ], iris: "https://schema.org/description")]
    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotBlank(message: "product_comment.comment.not_blank")]
    public ?string $comment = null;


    #[Groups(["product_comment:item:read", "write"])]
    #[ApiProperty(schema: [
        'type' => 'string',
        'example' => '/api/products/3fa85f64-5717-4562-b3fc-2c963f66afa6',
    ])]
    #[Assert\NotBlank(message: "product_comment.product.not_null")]
    #[ORM\ManyToOne(targetEntity: Product::class, cascade: ["remove"])]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false, onDelete: "CASCADE")]
    public ?Product $product = null;


    #[Groups(["write", "read"])]
    #[ORM\Column(type: 'float', nullable: false)]
    #[ApiProperty(schema: [
        'type' => 'float',
        'min' => 1,
        'max' => 5,
        'step' => 0.5,
        'example' => 5,
        'required' => true
    ], iris: "https://schema.org/Rating")]
    #[Assert\NotNull(message: "product_comment.rating.not_null")]
    #[Assert\Range(notInRangeMessage: "product_comment.rating.not_in_range", min: 1, max: 5)]
    #[Assert\DivisibleBy(value: 0.5, message: "product_comment.rating.divisible_by")]
    public ?float $rating = null;

    #[Groups(["read"])]
    #[ORM\Column(type: 'datetimetz_immutable', nullable: false)]
    #[ApiProperty(
        writable: false,
        schema: [
            'readonly' => true,
            'type' => 'datetime',
        ], iris: "https://schema.org/Date")]
    public ?DateTimeImmutable $createdDate = null;


    #[Groups(["read", "role_admin:write"])]
    #[ORM\Column(type: 'boolean', nullable: false)]
    #[ApiProperty(
       // security: "is_granted('VIEW',object)",
       // securityPostDenormalize: "is_granted('MODERATE',object)",
        schema: [
            'type' => 'boolean',
        ])]
    public bool $isModerated = false;


    public function __construct(array $data = [])
    {
        $this->createdDate = new DateTimeImmutable();

        parent::__construct($data);
    }

    public static function isPrivate(): bool
    {
        return false;
    }

}

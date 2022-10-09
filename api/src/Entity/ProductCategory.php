<?php

namespace App\Entity;


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
use App\Repository\ProductCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
#[ORM\Table(name: "product_category")]
#[ApiResource(operations: [
    new GetCollection(
        order: [
            "name" => "ASC"
        ],
        security: "is_granted('PUBLIC_ACCESS')",
    ),
    new Get(security: "is_granted('VIEW',object)"),
    new Post(securityPostDenormalize: "is_granted('CREATE',object)"),
    new Put(security: "is_granted('UPDATE',object)"),
    new Delete(security: "is_granted('DELETE',object)")
])]
#[ApiFilter(StatusEntityFilter::class, properties: ['archived'])]
class ProductCategory extends Entity implements IStatusEntity, INamedEntity
{

    use StatusTrait;

    #[Groups(["product", "product_category"])]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[ApiProperty(schema: [
        'type' => 'string',
        'maxLength' => 255,
        'minLength' => 1,
        'example' => 'Man shoes',
        'required' => true
    ], iris: "https://schema.org/name")]
    public ?string $name = null;

    #[Groups(["product_category"])]
    #[ORM\Column(type: 'text', nullable: false)]
    #[ApiProperty(schema: [
        'type' => 'string',
        'example' => 'The description of the product category',
        'required' => true
    ], iris: "https://schema.org/description")]
    public ?string $description = null;

    /**
     * @var Collection<int,Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: "categories")]
    #[Link(toProperty: "categories")]
    #[ApiProperty(readable: false, writable: false)]
    private Collection $products;


    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->products = new ArrayCollection();
    }

    /**
     * @return Collection<int,Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @param Collection<int,Product> $products
     */
    public function setProducts(Collection $products): void
    {
        $this->products = $products;
    }


    public function addProduct(Product $product): void
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }
    }

    public function removeProduct(Product $product): void
    {
        $this->products->removeElement($product);
    }

    public function __toString(): string
    {
        return (string)$this->name;
    }


}

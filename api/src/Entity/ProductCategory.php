<?php

namespace App\Entity;


use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ProductCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
#[ORM\Table(name: "product_category")]
#[ApiResource(operations: [
    new GetCollection(),
    new Get(),
    new Post(),
    new Post(),
    new Put(),
    new Delete()
])]
#[ApiResource(
    uriTemplate: '/product_categories/{productCategoryId}/products',
    uriVariables: [
        'productCategoryId' => new Link(fromClass: Product::class, toProperty: 'products'),
    ],
    operations: [new GetCollection()]
)]
class ProductCategory extends Entity
{

    #[Groups(["product", "product_category"])]
    #[ORM\Column(type: 'string', nullable: false, length: 255)]
    #[ApiProperty(iris: "https://schema.org/name")]
    public ?string $name = null;

    #[Groups(["product_category"])]
    #[ORM\Column(type: 'text', nullable: false)]
    #[ApiProperty(iris: "https://schema.org/description")]
    public ?string $description = null;

    /**
     * @var Collection<int,Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: "categories")]
    #[Link(toProperty: "categories")]
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

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "product_category")]
class ProductCategory extends Entity
{

    #[Groups(["product","product_category"])]
    #[ORM\Column(type: 'string', nullable: false, length: 255)]
    #[ApiProperty(iri: "https://schema.org/name")]
    public ?string $name = null;

    #[Groups(["product_category"])]
    #[ORM\Column(type: 'text', nullable: false)]
    #[ApiProperty(iri: "https://schema.org/description")]
    public ?string $description = null;

    /**
     * @var Collection<int,Product>
     */
    #[ApiSubresource(maxDepth: 1)]
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: "categories")]
    private Collection $products;


    public function __construct()
    {
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


    public function addProduct(Product $product) : void
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }
    }

    public function removeProduct(Product $product) : void
    {
        $this->products->removeElement($product);
    }


}

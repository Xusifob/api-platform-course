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
#[ORM\Table(name: "product")]
class Product extends Entity
{

    #[Groups(["product"])]
    #[ORM\Column(type: 'string', nullable: false, length: 255)]
    #[ApiProperty(iri: "https://schema.org/name")]
    #[Assert\NotBlank(message: "product.name.not_blank")]
    public ?string $name = null;

    #[Groups(["product:item"])]
    #[ORM\Column(type: 'text', nullable: false)]
    #[ApiProperty(iri: "https://schema.org/description")]
    #[Assert\NotBlank(message: "product.description.not_blank")]
    public ?string $description = null;

    /**
     * @var Collection<int,ProductCategory>
     */
    #[Groups(["product"])]
    #[ORM\ManyToMany(targetEntity: ProductCategory::class, inversedBy: "products")]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    private Collection $categories;

    #[Groups(["product"])]
    #[ORM\Column(type: 'smallint', nullable: false)]
    #[ApiProperty(iri: "https://schema.org/price")]
    #[Assert\NotNull(message: "product.price.not_null")]
    #[Assert\Range(minMessage: "product.price.min", min: 0)]
    public ?int $price = null;


    public function __construct()
    {
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

}

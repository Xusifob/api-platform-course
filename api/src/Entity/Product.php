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
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\NotEmpty;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(operations: [
    new GetCollection(),
    new Get(),
    new Post(),
    new Post(),
    new Put(),
    new Delete()
])]
#[ORM\Table(name: "product")]
class Product extends Entity
{

    #[Groups(["product"])]
    #[ORM\Column(type: 'string', nullable: false, length: 255)]
    #[ApiProperty(iris: "https://schema.org/name")]
    #[Assert\NotBlank(message: "product.name.not_blank")]
    public ?string $name = null;

    #[Groups(["product:item"])]
    #[ORM\Column(type: 'text', nullable: false)]
    #[ApiProperty(iris: "https://schema.org/description")]
    #[Assert\NotBlank(message: "product.description.not_blank")]
    public ?string $description = null;

    /**
     * @var Collection<int,ProductCategory>
     */
    #[Groups(["product"])]
    #[Link(toProperty: "products")]
    #[ORM\ManyToMany(targetEntity: ProductCategory::class, inversedBy: "products",cascade: ["remove"])]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    #[NotEmpty(message: "product.category.not_null")]
    private Collection $categories;

    #[Groups(["product"])]
    #[ORM\Column(type: 'smallint', nullable: false)]
    #[ApiProperty(iris: "https://schema.org/price")]
    #[Assert\NotNull(message: "product.price.not_null")]
    #[Assert\Range(minMessage: "product.price.min", min: 0)]
    public ?int $price = null;


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


    public function __toString(): string
    {
        return (string)$this->name;
    }

}

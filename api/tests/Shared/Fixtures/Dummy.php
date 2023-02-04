<?php

declare(strict_types=1);

declare(strict_types=1);

namespace App\Tests\Shared\Fixtures;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\IEntity;
use App\Entity\Trait\EntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ApiResource()]
#[ORM\Entity]
class Dummy implements IEntity
{

    use EntityTrait;

    /**
     * @var string The dummy name
     */
    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $name;

    public function __construct(array $data = [])
    {
        $this->setEntityData($data);
    }

    public function getId(): ?string
    {
        return $this->id;
    }


}

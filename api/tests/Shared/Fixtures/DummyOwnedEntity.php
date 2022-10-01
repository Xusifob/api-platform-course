<?php

declare(strict_types=1);

namespace App\Tests\Shared\Fixtures;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\IOwnedEntity;
use App\Entity\Trait\OwnedTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ApiResource()]
#[ORM\Entity]
class DummyOwnedEntity extends Dummy implements IOwnedEntity
{

    use OwnedTrait;

    /**
     * @var string The dummy name
     */
    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $name;

}

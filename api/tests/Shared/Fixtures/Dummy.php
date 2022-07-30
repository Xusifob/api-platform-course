<?php

declare(strict_types=1);

namespace App\Tests\Shared\Fixtures;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ApiResource()]
#[ORM\Entity]
class Dummy
{

    #[ORM\Column(type: 'uuid', unique: true, nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public string $id;

    /**
     * @var string The dummy name
     */
    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $name;

}

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
use App\Entity\Enum\NotificationType;
use App\Repository\ProductRepository;
use App\Validator\IsReference;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity()]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Post(),
        new Put(),
        new Delete()
    ])]
#[ORM\Table(name: "notification")]
class Notification extends Entity implements \Stringable
{

    #[Groups(["read"])]
    #[ORM\Column(type: 'string', length: 30, nullable: false)]
    #[Assert\NotNull(message: "notification.type.not_null")]
    public NotificationType $type;


    #[Groups(["notification:write", "read"])]
    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Assert\NotNull(message: "notification.read.not_null")]
    public bool $read = false;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function __toString(): string
    {
        return $this->type->value;
    }

}

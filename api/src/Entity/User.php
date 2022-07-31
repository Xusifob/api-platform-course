<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Enum\UserRole;
use App\Entity\Trait\StatusTrait;
use App\Repository\UserRepository;
use App\State\User\MeProvider;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    uriTemplate: '/users/me',
    operations: [new Get()],
    provider: MeProvider::class
)]
#[ORM\EntityListeners(["App\Doctrine\EntityListener\UserListener"])]
class User extends Entity implements IEntity, IStatusEntity, UserInterface, PasswordAuthenticatedUserInterface
{

    use StatusTrait;

    #[Groups(["user:item"])]
    #[ORM\Column(length: 180, unique: true)]
    public ?string $email = null;

    /**
     * @var string[]
     */
    #[ORM\Column(name: "roles",)]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(name: "password", nullable: false)]
    private ?string $password = null;

    public ?string $plainPassword = null;

    #[Groups(["user:item"])]
    #[ORM\Column(length: 255, nullable: true)]
    public ?string $givenName = null;

    #[Groups(["user:item"])]
    #[ORM\Column(length: 255, nullable: true)]
    public ?string $familyName = null;

    #[Groups(["user:item"])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    public ?DateTimeInterface $birthDate = null;

    public function getId(): string|null
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = UserRole::ROLE_USER->value;

        return array_unique($roles);
    }


    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }


    public function setRole(string|UserRole $role): self
    {
        $roles = $this->roles;

        $roles[] = $role instanceof UserRole ? $role->value : $role;

        return $this->setRoles($roles);
    }


    #[Groups(["user:item"])]
    public function getRole(): UserRole
    {
        $role = $this->getRoles()[0];
        return UserRole::from($role);
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function __toString(): string
    {
        return "$this->givenName $this->familyName";
    }

}

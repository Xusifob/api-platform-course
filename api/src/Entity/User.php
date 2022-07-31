<?php

namespace App\Entity;

use App\Entity\Enum\UserRole;
use App\Entity\Trait\StatusTrait;
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\EntityListeners(["App\Doctrine\EntityListener\UserListener"])]
class User extends Entity implements IEntity, IStatusEntity, UserInterface, PasswordAuthenticatedUserInterface
{

    use StatusTrait;

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

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $givenName = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $familyName = null;

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

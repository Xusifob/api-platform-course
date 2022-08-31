<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Doctrine\EntityListener\UserListener;
use App\Entity\Enum\UserRole;
use App\Entity\Trait\StatusTrait;
use App\Repository\UserRepository;
use App\State\User\MeProvider;
use App\State\User\UserProcessor;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    uriTemplate: '/users/me',
    operations: [new Get()],
    provider: MeProvider::class
)]
#[ApiResource(
    uriTemplate: '/users',
    operations: [new Post()],
    processor: UserProcessor::class
)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
)]
#[ORM\EntityListeners([UserListener::class])]
class User extends Entity implements IEntity, IStatusEntity, UserInterface, PasswordAuthenticatedUserInterface
{

    use StatusTrait;

    #[Groups(["user:item"])]
    #[ApiProperty(schema: [
        'type' => 'string',
        'maxLength' => 180,
        'example' => 'john@doe.fr',
        'required' => true
    ], iris: "https://schema.org/email")]
    #[ORM\Column(length: 180, unique: true)]
    public ?string $email = null;

    /**
     * @var string[]
     */
    #[ORM\Column(name: "roles", type: 'json', options: ['jsonb' => true])]
    #[ApiProperty(readable: false, writable: false)]
    private array $roles = [];

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column(name: "password", nullable: false)]
    #[ApiProperty(readable: false, writable: false)]
    private ?string $password = null;

    #[Groups("user:post")]
    #[SerializedName("password")]
    #[ApiProperty(schema: [
        'type' => 'string',
        'maxLength' => 30,
        'example' => 'My@WesomeP@$$w0rd',
        'required' => true
    ], iris: "https://schema.org/accessCode")]
    public ?string $plainPassword = null;

    #[Groups(["user:item"])]
    #[ApiProperty(schema: [
        'type' => 'string',
        'maxLength' => 255,
        'example' => 'John',
        'required' => false
    ], iris: "https://schema.org/givenName")]
    #[ORM\Column(length: 255, nullable: true)]
    public ?string $givenName = null;

    #[Groups(["user:item"])]
    #[ApiProperty(schema: [
        'type' => 'string',
        'maxLength' => 255,
        'example' => 'DOE',
        'required' => false
    ], iris: "https://schema.org/familyName")]
    #[ORM\Column(length: 255, nullable: true)]
    public ?string $familyName = null;

    #[Groups(["user:item"])]
    #[ApiProperty(schema: [
        'type' => 'date',
        'example' => '1995-06-07',
        'required' => false
    ], iris: "https://schema.org/birthDate")]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    public ?DateTimeInterface $birthDate = null;

    public function getId(): string|null
    {
        return $this->id;
    }


    #[ApiProperty(schema: [
        'type' => 'string',
        'example' => 'John DOE',
        'writable' => false
    ], iris: "https://schema.org/name")]
    public function getFullName(): string
    {
        return "$this->givenName $this->familyName";
    }


    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[ApiProperty(readable: false, writable: false)]
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
    #[ApiProperty(schema: [
        'type' => 'string',
        'enum' => UserRole::CASES,
        'example' => UserRole::CASES[0],
        'required' => false
    ], iris: "https://schema.org/roleName")]
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
        return $this->getFullName();
    }


    private function isRole(UserRole $role): bool
    {
        return in_array($role->value, $this->roles);
    }

    #[ApiProperty(readable: false, writable: false)]
    public function isAdmin(): bool
    {
        return $this->isRole(UserRole::ROLE_ADMIN);
    }

    #[ApiProperty(readable: false, writable: false)]
    public function isCustomer(): bool
    {
        return $this->isRole(UserRole::ROLE_CUSTOMER);
    }


}

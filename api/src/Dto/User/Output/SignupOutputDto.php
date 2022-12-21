<?php

namespace App\Dto\User\Output;

use App\Entity\Enum\UserRole;
use Symfony\Component\Serializer\Annotation\Groups;

class SignupOutputDto
{

    #[Groups("read")]
    public ?string $email = null;

    #[Groups("read")]
    public ?string $givenName = null;

    #[Groups("read")]
    public ?string $familyName = null;

    #[Groups("read")]
    public ?UserRole $role = null;

    #[Groups("read")]
    public bool $activationEmailSent = false;


}

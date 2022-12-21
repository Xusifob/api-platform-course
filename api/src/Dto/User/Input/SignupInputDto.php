<?php

namespace App\Dto\User\Input;

use App\Dto\RepeatPasswordInterface;
use App\Validator\IsPasswordValid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[IsPasswordValid(
    notMatchPasswordMessage: "user.password.not_match",
    weakPasswordMessage: "user.password.weak"
)]
class SignupInputDto implements RepeatPasswordInterface
{

    #[Groups("write")]
    #[Assert\NotBlank(message: "user.email.invalid")]
    #[Assert\Email(message: "user.email.invalid")]
    public ?string $email = null;

    #[Groups("write")]
    #[Assert\NotCompromisedPassword(message: "user.password.invalid")]
    public ?string $password = null;

    #[Groups("write")]
    public ?string $repeatPassword = null;

    #[Groups("write")]
    #[Assert\NotBlank(message: "user.given_name.invalid")]
    public ?string $givenName = null;

    #[Groups("write")]
    #[Assert\NotBlank(message: "user.family_name.invalid")]
    public ?string $familyName = null;


    public function getPassword(): string
    {
        return (string)$this->password;
    }


    public function isPasswordRepeated(): bool
    {
        return $this->password && ($this->password === $this->repeatPassword);
    }


}

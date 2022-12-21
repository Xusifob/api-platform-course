<?php

namespace App\Dto;

interface RepeatPasswordInterface
{


    public function getPassword(): string;


    public function isPasswordRepeated(): bool;
}

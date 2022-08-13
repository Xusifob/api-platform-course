<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\IRepository;
use Doctrine\ORM\EntityManagerInterface;

trait TesterTrait
{

    protected EntityManagerInterface|null $em;



    protected function getRepository(string $class = null): IRepository
    {
        $this->em->clear();

        return $this->em->getRepository($this->getClass($class));
    }



    protected function getCustomer(): User
    {
        return $this->getUser("customer");
    }


    protected function getAdmin(): User
    {
        return $this->getUser("admin");
    }

    protected function getUser(string $username): User
    {
        return $this->getRepository(User::class)->findOneBy(['email' => $this->resolveUsername($username)]);
    }


    protected function resolveUsername(string $username): string
    {
        return match ($username) {
            "admin" => "admin@api-platform-course.com",
            "customer" => "customer1@api-platform-course.com",
            "customer2" => "customer2@api-platform-course.com",
            default => $username
        };
    }

    /**
     * @return string
     */
    public function getClass(string $class = null): string
    {
        if ($class) {
            return $class;
        }

        return $this->getDefaultClass();
    }


}

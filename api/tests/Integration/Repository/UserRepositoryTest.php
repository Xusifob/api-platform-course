<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{

    private readonly UserRepository $repository;

    protected EntityManagerInterface|null $em;

    use ReloadDatabaseTrait;


    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->em->getRepository($this->getClass());
    }


    public function testFindByRole()
    {
        $byRole = $this->repository->findByRole(UserRole::ROLE_ADMIN);

        $this->assertCount(1, $byRole);
        $this->assertEquals(UserRole::ROLE_ADMIN, $byRole[0]->getRole());

        $byRole = $this->repository->findByRole(UserRole::ROLE_CUSTOMER);

        $this->assertCount(2, $byRole);
        foreach ($byRole as $item) {
            $this->assertEquals(UserRole::ROLE_CUSTOMER, $item->getRole());
        }
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }


    private function getClass(): string
    {
        return User::class;
    }

}

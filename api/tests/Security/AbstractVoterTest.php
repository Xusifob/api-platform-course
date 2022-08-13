<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Tests\TesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

abstract class AbstractVoterTest extends KernelTestCase
{

    protected VoterInterface $voter;

    use TesterTrait;


    public function setUp(): void
    {
        self::bootKernel();
        $this->voter = self::getContainer()->get($this->getVoter());
        $this->em = self::getContainer()->get('doctrine')->getManager();
    }


    protected function buildToken(string $email): UsernamePasswordToken
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => "$email@api-platform-course.com"]);
        return new UsernamePasswordToken($user, 'none', $user->getRoles());
    }

    protected function assertVote(string $username, $subject, string $attribute, int $access): void
    {
        $result = $this->voter->vote($this->buildToken($username), $subject, [$attribute]);

        $this->assertEquals($access, $result);
    }

    abstract public function getVoter(): string;

}

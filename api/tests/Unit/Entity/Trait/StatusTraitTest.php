<?php

namespace App\Tests\Unit\Entity\Trait;

use App\Entity\Enum\EntityStatus;
use App\Entity\Trait\StatusTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StatusTraitTest extends KernelTestCase
{

    public function testArchive(): void
    {
        $object = $this->getObjectForTrait(StatusTrait::class);

        $object->archive();

        $this->assertTrue($object->isArchived());
        $this->assertEquals(EntityStatus::ARCHIVED, $object->getStatus());
    }

    public function testDelete(): void
    {
        $object = $this->getObjectForTrait(StatusTrait::class);

        $object->delete();

        $this->assertTrue($object->isDeleted());
        $this->assertEquals(EntityStatus::DELETED, $object->getStatus());
    }

    public function testActivate(): void
    {
        $object = $this->getObjectForTrait(StatusTrait::class);
        $object->setStatus(EntityStatus::DELETED);

        $object->enable();

        $this->assertTrue($object->isActive());
        $this->assertEquals(EntityStatus::ACTIVE, $object->getStatus());
    }


}

<?php

namespace CarlBennett\Tools\Tests\Libraries\Factorio;

use \CarlBennett\Tools\Libraries\Factorio\Entity;
use \PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    private static function entity(string $name, int $direction, float $x = 1.5, float $y = -2.5): Entity
    {
        $data = ['name' => $name, 'direction' => $direction, 'position' => ['x' => $x, 'y' => $y]];
        return new Entity($data);
    }

    public function testReflectHorizontalBasic(): void
    {
        $e = self::entity('inserter', Entity::DIRECTION_EAST);
        $this->assertSame('', $e->reflectHorizontal());
        $this->assertSame(Entity::DIRECTION_WEST, $e->getDirection());
        $this->assertSame(-1.5, $e->getPositionX());
        $this->assertSame(-2.5, $e->getPositionY());
    }

    public function testReflectVerticalBasic(): void
    {
        $e = self::entity('inserter', Entity::DIRECTION_NORTH);
        $this->assertSame('', $e->reflectVertical());
        $this->assertSame(Entity::DIRECTION_SOUTH, $e->getDirection());
        $this->assertSame(1.5, $e->getPositionX());
        $this->assertSame(2.5, $e->getPositionY());
    }

    public function testReflectingTwiceRestoresEveryDirection(): void
    {
        foreach (['inserter', 'curved-rail'] as $name)
        {
            for ($d = Entity::DIRECTION_NORTH; $d <= Entity::DIRECTION_NORTH_WEST; $d++)
            {
                $h = self::entity($name, $d);
                $h->reflectHorizontal();
                $h->reflectHorizontal();
                $this->assertSame($d, $h->getDirection(), "$name horizontal $d");

                $v = self::entity($name, $d);
                $v->reflectVertical();
                $v->reflectVertical();
                $this->assertSame($d, $v->getDirection(), "$name vertical $d");
            }
        }
    }

    public function testReflectStorageTank(): void
    {
        $e = self::entity('storage-tank', 0);
        $e->reflectHorizontal();
        $this->assertSame(2, $e->getDirection());
    }

    public function testUnknownDirection(): void
    {
        $this->assertSame('Unknown entity direction.', self::entity('inserter', 9)->reflectHorizontal());
    }

    public function testEntityWritesThroughToData(): void
    {
        $data = ['name' => 'inserter', 'direction' => Entity::DIRECTION_EAST, 'position' => ['x' => 1, 'y' => 1]];
        (new Entity($data))->reflectHorizontal();
        $this->assertSame(Entity::DIRECTION_WEST, $data['direction']);
        $this->assertSame(-1, $data['position']['x']);
    }
}

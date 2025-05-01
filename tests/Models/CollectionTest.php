<?php

namespace App\CommissionTask\Tests\Models;

use App\CommissionTask\Models\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
    /**
     * @var Collection<string>
     */
    private Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collection = new Collection();
    }

    public function testAdd(): void
    {
        $item = 'testItem';
        $this->collection->add($item);

        $this->assertTrue($this->collection->contains($item));
        $this->assertCount(1, $this->collection->all());
    }

    public function testRemove(): void
    {
        $item = 'testItem';
        $this->collection->add($item);
        $this->collection->remove($item);

        $this->assertFalse($this->collection->contains($item));
        $this->assertCount(0, $this->collection->all());
    }

    public function testContains(): void
    {
        $item = 'testItem';
        $this->assertFalse($this->collection->contains($item));

        $this->collection->add($item);
        $this->assertTrue($this->collection->contains($item));
    }

    public function testAll(): void
    {
        $item1 = 'item1';
        $item2 = 'item2';

        $this->collection->add($item1);
        $this->collection->add($item2);

        $this->assertEquals([$item1, $item2], $this->collection->all());
    }

    public function testCount(): void
    {
        $this->assertSame(0, $this->collection->count());

        $this->collection->add('item1');
        $this->collection->add('item2');

        $this->assertSame(2, $this->collection->count());
    }
}

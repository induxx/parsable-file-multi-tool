<?php

namespace Tests\Misery\Component\Common\Collection;

use Misery\Component\Common\Collection\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ArrayCollectionTest extends TestCase
{
    private $items = [
        [
            'id' => '1',
            'first_name' => 'Gordie',
        ],
        [
            'id' => "2",
            'first_name' => 'Frans',
        ],
    ];

    public function test_collection_get_values(): void
    {
        $collection = new ArrayCollection($this->items);

        $this->assertSame($collection->getValues(), $this->items);
    }


    public function test_collection_map_values(): void
    {
        $collection = new ArrayCollection($this->items);

        $result = $collection->map(function ($item) {
            $new['id'] = $item['id'];
            return $new;
        });

        $this->assertSame($result->getValues(), [['id' => '1'], ['id' => '2']]);
    }

    public function test_collection_filter_values(): void
    {
        $collection = new ArrayCollection($this->items);

        $result = $collection->filter(function ($item) {
            return $item['id'] === '1';
        });

        $this->assertSame($result->getValues(), [['id' => '1', 'first_name' => 'Gordie']]);
    }

    public function test_collection_count_values(): void
    {
        $collection = new ArrayCollection($this->items);

        $this->assertSame($collection->count(), 2);

        $collection = new ArrayCollection();

        $this->assertSame($collection->count(), 0);
    }

    public function test_collection_first_values(): void
    {
        $collection = new ArrayCollection($this->items);

        $this->assertSame($collection->first(), ['id' => '1', 'first_name' => 'Gordie']);
    }

    public function test_collection_has_values(): void
    {
        $collection = new ArrayCollection($this->items);

        $this->assertTrue($collection->hasValues());

        $collection = new ArrayCollection();

        $this->assertFalse($collection->hasValues());
    }

    public function test_collection_adds_values(): void
    {
        $collection = new ArrayCollection($this->items);

        $collection->add(['id' => '3', 'first_name' => 'Simon']);

        $this->assertSame($collection->count(), 3);
    }

    public function test_collection_sets_values(): void
    {
        $collection = new ArrayCollection($this->items);

        $collection->set(3, ['id' => '3', 'first_name' => 'Simon']);

        $this->assertSame($collection->count(), 3);
    }

    public function test_collection_get_value(): void
    {
        $collection = new ArrayCollection($this->items);

        $result = $collection->get(1);

        $this->assertSame(
            $result->first(),
            [
                'id' => "2",
                'first_name' => 'Frans',
            ]
        );
    }

    public function test_collection_contains_key(): void
    {
        $collection = new ArrayCollection($this->items);
        $this->assertTrue($collection->containsKey(0));
        $this->assertTrue($collection->containsKey(1));
        $this->assertFalse($collection->containsKey(2));
        $collection->add(['id' => '3', 'first_name' => 'Simon']);
        $this->assertTrue($collection->containsKey(2));
    }

    public function test_collection_remove(): void
    {
        $collection = new ArrayCollection($this->items);
        $this->assertSame(2, $collection->count());
        $collection->remove(0);
        $this->assertSame(1, $collection->count());
        $this->assertFalse($collection->containsKey(0));
        $this->assertTrue($collection->containsKey(1));
    }

    public function test_collection_to_array(): void
    {
        $collection = new ArrayCollection($this->items);
        $array = $collection->toArray();
        $this->assertIsArray($array);
        $this->assertSame($this->items, $array);
    }
}
<?php

namespace Tests\Misery\Component\Common\Cursor;

use Misery\Component\Common\Cursor\OldCachedZoneFetcher;
use Misery\Component\Reader\ItemCollection;
use PHPUnit\Framework\TestCase;

class OldCachedZoneFetcherTest extends TestCase
{
    public function testFetchItemFromCache()
    {
        $data = [
            ["id" => "A1", "name" => "Item A1"],
            ["id" => "B1", "name" => "Item B1"],
            ["id" => "C1", "name" => "Item C1"],
            ["id" => "D1", "name" => "Item D1"],
        ];

        $items = new ItemCollection($data);

        $fetcher = new OldCachedZoneFetcher($items, 'id');

        $this->assertEquals(["id" => "B1", "name" => "Item B1"], $fetcher->get("B1"));
        $this->assertFalse($fetcher->get("Z99"));
    }

    public function testFetchItemFromCacheExtreme()
    {
        $data = [];
        $numItems = 10000;
        for ($i = 0; $i < $numItems; $i++) {
            $id = "ID" . str_pad($i, 5, "0", STR_PAD_LEFT);
            $data[] = ["id" => $id, "name" => "Item $id"];
        }

        $items = new ItemCollection($data);

        $fetcher = new OldCachedZoneFetcher($items, 'id');

        $randomIndex = rand(0, $numItems - 1);
        $randomId = "ID" . str_pad($randomIndex, 5, "0", STR_PAD_LEFT);
        $this->assertEquals(["id" => $randomId, "name" => "Item $randomId"], $fetcher->get($randomId));

        $this->assertFalse($fetcher->get("NON_EXISTENT_ID"));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Modifier;

use Misery\Component\Modifier\CleanseModifier;
use PHPUnit\Framework\TestCase;

class CleanseModifierTest extends TestCase
{
    public function testRemoveAnyCharacters()
    {
        $modifier = new CleanseModifier();
        $modifier->setOptions(['remove_any' => ['a', 'b']]);
        $this->assertEquals('cde', $modifier->modify('abcde'));
    }

    public function testRemoveStartSubstring()
    {
        $modifier = new CleanseModifier();
        $modifier->setOptions(['remove_start' => ['foo']]);
        $this->assertEquals('bar', $modifier->modify('foobar'));
        $this->assertEquals('barfoobar', $modifier->modify('barfoobar'));
    }

    public function testRemoveEndSubstring()
    {
        $modifier = new CleanseModifier();
        $modifier->setOptions(['remove_end' => ['foo']]);
        $this->assertEquals('foobar', $modifier->modify('foobar'));
        $this->assertEquals('foobar', $modifier->modify('foobarfoo'));
    }

    public function testCleanWholeSubstring()
    {
        $modifier = new CleanseModifier();
        $modifier->setOptions(['clean_whole' => ['delete']]);
        $this->assertEquals('', $modifier->modify('please delete this'));
        $this->assertEquals('keep', $modifier->modify('keep'));
    }

    public function testModifyArrayRecursively()
    {
        $modifier = new CleanseModifier();
        $modifier->setOptions(['remove_any' => ['x']]);
        $input = ['xray', 'box', 'text'];
        $expected = ['ray', 'bo', 'tet'];
        $this->assertEquals($expected, $modifier->modify($input));
    }

    public function testNonStringValuesAreUnchanged()
    {
        $modifier = new CleanseModifier();
        $modifier->setOptions(['remove_any' => ['x']]);
        $this->assertSame(123, $modifier->modify(123));
        $this->assertSame(null, $modifier->modify(null));
        $this->assertSame(true, $modifier->modify(true));
    }

    public function testRemoveAnyDoesNotConcatenateLines()
    {
        $modifier = new CleanseModifier();
        $modifier->setOptions(['remove_any' => ['Pdmarticle:']]);
        $input = ["Pdmarticle:39\nPdmarticle:238\nPdmarticle:239"];
        $expected = ["39\n238\n239"];
        $this->assertEquals($expected, $modifier->modify($input));
    }
}

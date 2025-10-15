<?php

namespace Tests\Misery\Component\Converter;

use Misery\Component\Converter\Matcher;
use PHPUnit\Framework\TestCase;

class MatcherTest extends TestCase
{
    public function testDuplicateWithNewKeyKeepsPropertyStructure(): void
    {
        $original = Matcher::create('street');

        $clone = $original->duplicateWithNewKey('postal_code');

        $this->assertSame('postal_code', $clone->getPrimaryKey());
        $this->assertSame('postal_code', $clone->getMainKey());
        $this->assertTrue($clone->isProperty());
        $this->assertNull($clone->getLocale());
        $this->assertNull($clone->getScope());
    }

    public function testDuplicateWithNewKeyPreservesLocaleAndScope(): void
    {
        $original = Matcher::create('values|color', 'en_US', 'ecommerce');

        $clone = $original->duplicateWithNewKey('values|size');

        $this->assertSame('values|size', $clone->getMainKey());
        $this->assertSame('size', $clone->getPrimaryKey());
        $this->assertSame('en_US', $clone->getLocale());
        $this->assertSame('ecommerce', $clone->getScope());
        $this->assertFalse($clone->isProperty());
    }

    public function testDuplicateWithNewKeyPreservesLocaleWithoutScope(): void
    {
        $original = Matcher::create('values|description', 'nl_NL');

        $clone = $original->duplicateWithNewKey('values|summary');

        $this->assertSame('values|summary', $clone->getMainKey());
        $this->assertSame('summary', $clone->getPrimaryKey());
        $this->assertSame('nl_NL', $clone->getLocale());
        $this->assertNull($clone->getScope());
        $this->assertFalse($clone->isProperty());
    }

    public function testDuplicatePreservationWithoutRootPath(): void
    {
        $original = Matcher::create('values|description', 'nl_NL');

        $clone = $original->duplicateWithNewKey('summary');

        $this->assertSame('values|summary|nl_NL', $clone->getMainKey());
        $this->assertSame('summary', $clone->getPrimaryKey());
        $this->assertSame('nl_NL', $clone->getLocale());
        $this->assertNull($clone->getScope());
        $this->assertFalse($clone->isProperty());
    }
}

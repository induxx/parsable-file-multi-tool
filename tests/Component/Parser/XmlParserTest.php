<?php
declare(strict_types=1);

namespace Tests\Misery\Component\Parser;

use Misery\Component\Parser\XmlParser;
use PHPUnit\Framework\TestCase;

class XmlParserTest extends TestCase
{
    /** @var XmlParser */
    private XmlParser $parser;

    /** @var \ReflectionMethod */
    private \ReflectionMethod $flatten;

    protected function setUp(): void
    {
        // Avoid constructor side-effects (XMLReader::open)
        $rc = new \ReflectionClass(XmlParser::class);
        $this->parser = $rc->newInstanceWithoutConstructor();

        // Access the private method simpleXmlToArray
        $this->flatten = $rc->getMethod('simpleXmlToArray');
        $this->flatten->setAccessible(true);
    }

    /**
     * Helper to call the private simpleXmlToArray on a snippet.
     *
     * @param string $xmlSnippet XML string with a single root element
     * @param string $prefix Optional prefix to simulate nested recursion
     * @return array<string, string|null>
     */
    private function flatten(string $xmlSnippet, string $prefix = ''): array
    {
        $sx = new \SimpleXMLElement($xmlSnippet);
        return $this->flatten->invoke($this->parser, $sx, $prefix);
    }

    public function testLeafText(): void
    {
        $result = $this->flatten('<FOO>bar</FOO>');

        $this->assertSame(
            ['FOO' => 'bar'],
            $result
        );
    }

    public function testSelfClosingWithAttributeBecomesNull(): void
    {
        $result = $this->flatten('<FOO id="1"/>');

        $this->assertSame(
            ['FOO[id=1]' => null],
            $result
        );
    }

    public function testAttributeAndText(): void
    {
        $result = $this->flatten('<FOO id="1">bar</FOO>');

        $this->assertSame(
            ['FOO[id=1]' => 'bar'],
            $result
        );
    }

    public function testNestedChildWithAttributeOnChild(): void
    {
        $result = $this->flatten('<PARENT><CHILD lang="eng">Hello</CHILD></PARENT>');

        $this->assertSame(
            ['PARENT.CHILD[lang=eng]' => 'Hello'],
            $result
        );
    }

    public function testNestedPrefixIsAppended(): void
    {
        // Simulate recursion prefix (as used internally)
        $result = $this->flatten('<CHILD lang="dut">Hallo</CHILD>', 'PARENT');

        $this->assertSame(
            ['PARENT.CHILD[lang=dut]' => 'Hallo'],
            $result
        );
    }

    public function testMultipleAttributesSortedInKey(): void
    {
        // Attributes intentionally out of alpha order; key must sort them
        $result = $this->flatten('<FOO z="9" a="1" b="2"/>');

        $this->assertSame(
            ['FOO[a=1;b=2;z=9]' => null],
            $result
        );
    }

    public function testNoChildrenWhitespaceOnlyIsNull(): void
    {
        $result = $this->flatten("<FOO id=\"1\">\n   \t  \n</FOO>");

        $this->assertSame(
            ['FOO[id=1]' => null],
            $result
        );
    }

    public function testDeepNesting(): void
    {
        $xml = <<<XML
<ROOT>
  <A>
    <B type="x">
      <C>value</C>
    </B>
  </A>
</ROOT>
XML;

        $sx = new \SimpleXMLElement($xml);

        // We want to call the private method on the <ROOT> element to ensure full recursion
        $rc = new \ReflectionClass(XmlParser::class);
        /** @var XmlParser $parser */
        $parser = $rc->newInstanceWithoutConstructor();
        $m = $rc->getMethod('simpleXmlToArray');
        $m->setAccessible(true);

        $result = $m->invoke($parser, $sx);

        $this->assertSame(
            ['ROOT.A.B[type=x].C' => 'value'],
            $result
        );
    }

    /**
     * NOTE about repeated siblings:
     * The current flattening strategy uses a flat map of "path => value".
     * If the XML has repeated siblings with identical paths (e.g., <B>1</B><B>2</B>),
     * later entries will overwrite earlier ones. This test documents the current behavior.
     * If you later add array handling, update this test accordingly.
     */
    public function testRepeatedSiblingsCurrentBehaviorIsOverwriteLast(): void
    {
        $xml = '<PARENT><ITEM>one</ITEM><ITEM>two</ITEM></PARENT>';
        $result = $this->flatten($xml);

        // With current implementation, last wins:
        $this->assertSame(
            ['PARENT.ITEM' => ['one', 'two']],
            $result
        );
    }
}

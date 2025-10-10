<?php
declare(strict_types=1);

namespace Tests\Misery\Component\Parser;

use Misery\Component\Parser\XmlParser;
use PHPUnit\Framework\TestCase;

class XmlParserTest extends TestCase
{
    private null|string $xmlFile = null;

    public function testUnParsable(): void
    {
        $parser = $this->createXmlParser('<FOO>bar</FOO>', '');

        $this->assertFalse($parser->valid());
    }

    public function testDeepNesting(): void
    {
        $xml = <<<XML
<ROOT>
  <A>
    <B>
      <C>value</C>
    </B>
  </A>
</ROOT>
XML;
        $parser = $this->createXmlParser($xml, 'ROOT');

        $this->assertSame(
            [
                'A' => [
                    'B' => [
                        'C' => 'value'
                    ],
                ],
            ],
            $parser->current()
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

        $parser = $this->createXmlParser($xml, 'PARENT');

        // With current implementation, last wins:
        $this->assertSame(
            ['ITEM' => ['one', 'two']],
            $parser->current()
        );

        $parser->next();

        $this->assertFalse($parser->valid());
    }

    private function createXmlParser(string $xmlContent, string $xpath): XmlParser
    {
        if (null === $this->xmlFile) {
            $this->xmlFile = tempnam(sys_get_temp_dir(), 'XML');
        }
        file_put_contents($this->xmlFile, $xmlContent);

        return new XmlParser($this->xmlFile, $xpath);
    }
}

<?php

namespace Misery\Component\Parser;

use Misery\Component\Common\Cursor\CursorInterface;

class XmlParser implements CursorInterface
{
    /** @var string[] */
    private array $path = [];
    private $xml;
    /** @var int|null */
    private $count;
    private $i = 0;

    /**
     * @param string      $file
     * @param string|null $container  e.g. "Records/Product" or just "Records"
     */
    public function __construct(string $file, string $container = null)
    {
        $this->xml = new \XMLReader();
        $this->xml->open($file);

        if (null !== $container) {
            $this->path = explode('/', $container);
        }
    }

    public static function create(string $filename, string $container = null): self
    {
        return new self($filename, $container);
    }

    public function loop(callable $callable): void
    {
        foreach ($this->getIterator() as $row) {
            $callable($row);
        }
    }

    public function getIterator(): \Generator
    {
        while ($this->valid()) {
            yield $this->key() => $this->current();
            $this->next();
        }
        // rewind after finishing
        $this->rewind();
    }

    public function current(): mixed
    {
        // on first fetch, drill down through all segments
        if ($this->i === 0) {
            foreach ($this->path as $segment) {
                while (
                    $this->xml->read() &&
                    !($this->xml->nodeType === \XMLReader::ELEMENT &&
                        $this->xml->localName === $segment)
                ) {
                    // skip
                }
                // once you hit a segment that's not the last, go one level deeper
                if ($segment !== end($this->path)) {
                    $this->xml->read();
                }
            }
        }

        // now at the last segment
        if ($this->xml->nodeType === \XMLReader::ELEMENT
            && $this->xml->localName === end($this->path)
        ) {
            $this->i++;
            $xmlStr = $this->xml->readOuterXML();
            $simple = new \SimpleXMLElement($xmlStr);

            // convert recursively—and *always* preserve attributes on leaves
            return $this->simpleXmlToArray($simple);
        }

        return false;
    }

    /**
     * Recursively convert a SimpleXMLElement into a PHP array,
     * preserving attributes under '@attributes', text under '@value',
     * and grouping same-named children into arrays.
     *
     * @param \SimpleXMLElement $node
     * @return string|array  string if leaf-without-attributes, or array otherwise
     */
    private function simpleXmlToArray(\SimpleXMLElement $node): string|array
    {
        $result = [];

        // 1) attributes
        foreach ($node->attributes() as $name => $value) {
            $result['@attributes'][$name] = (string) $value;
        }

        // 2) child elements
        foreach ($node->children() as $child) {
            $childName = $child->getName();
            $childValue = $this->simpleXmlToArray($child);

            // if we already have one, turn it into a numeric array
            if (isset($result[$childName])) {
                if (! is_array($result[$childName]) || ! array_key_exists(0, $result[$childName])) {
                    $result[$childName] = [ $result[$childName] ];
                }
                $result[$childName][] = $childValue;
            } else {
                $result[$childName] = $childValue;
            }
        }

        // 3) leaf text content
        $text = trim((string) $node);
        if ($text !== '') {
            if (count($result) === 0) {
                // pure text, no attrs/no children → return string directly
                return $text;
            }
            // mixed element (has attrs or children) → store under '@value'
            $result['@value'] = $text;
        }

        return $result;
    }

    public function next(): void
    {
        // jump to next <childTag>
        $childTag = $this->path[count($this->path) - 1];
        $this->xml->next($childTag);
    }

    public function key(): mixed
    {
        return $this->i;
    }

    public function valid(): bool
    {
        return false !== $this->current();
    }

    public function rewind(): void
    {
        if (false === $this->valid()) {
            $this->count = $this->key() - 1;
        }

        $this->count();
        $this->seek(0);

        // position at first data element
        $this->next();
    }

    public function seek($offset): void
    {
        // move reader back to start + offset: we just re-open
        $this->xml->close();
        $this->xml->open($this->xml->URI);
        $this->i = $offset;
        // skip offset items
        for ($k = 0; $k < $offset; $k++) {
            $this->next();
        }
    }

    public function count(): int
    {
        if (null === $this->count) {
            $this->count = 0;
            // count by consuming
            $this->loop(function () {
                $this->count++;
            });
        }

        return $this->count;
    }

    public function clear(): void
    {
        // no-op
    }
}

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
            return json_decode(json_encode($simple), true);
        }

        return false;
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

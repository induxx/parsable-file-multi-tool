<?php

namespace Misery\Component\Writer;

use XMLWriter;

class XmlStreamWriter implements ItemWriterInterface
{
    private ?XMLWriter $writer = null;
    private string $uri;
    private string $rootName;
    private ?string $containerName = null;
    private ?string $itemTagName = null;
    private bool $splitAttributeNodes = false;

    /**
     * XmlStreamWriter handles phased XML output: header, container, items, and closing.
     *
     * @param string $uri      File path to write to
     * @param array  $options  Document options: 'version', 'encoding', 'indent', 'indent_string'
     */
    public function __construct(string $uri, array $options = [])
    {
        $this->uri      = $uri;
        $rootContainer = $options['root_container'] ?? [];
        if ($rootContainer === []) {
            throw new \InvalidArgumentException('Option "root_container" must be provided in options.');
        }
        if (count($rootContainer) > 1) {
            throw new \InvalidArgumentException('Only one root container is allowed.');
        }
        $this->rootName = key($rootContainer);

        $headerContainer = $options['header_container'] ?? [];
        if (count($headerContainer) > 1) {
            throw new \InvalidArgumentException('Only one header container is allowed.');
        }
        $itemContainer = $options['xpath'] ?? null;
        if ($itemContainer === null) {
            throw new \InvalidArgumentException('Option "xpath" must be provided in options.');
        }
        $itemContainerParts = explode('|', $itemContainer);
        if ($this->rootName === $itemContainerParts[0]) {
            // If the item container is the same as the root, we don't need to start a new element
            unset($itemContainerParts[0]);
            $itemContainerParts = array_values(array_filter($itemContainerParts));
        }

        $this->splitAttributeNodes = $options['split_attribute_nodes'] ?? false;

        $version      = $options['version'] ?? '1.0';
        $encoding     = $options['encoding'] ?? 'UTF-8';
        $indent       = $options['indent'] ?? 2;
        $indentString = str_repeat($options['indent_string'] ?? ' ', $indent);

        $writer = new XMLWriter();
        if (@$writer->openUri($uri) !== true) {
            throw new \RuntimeException("Unable to open URI for writing: $uri");
        }
        $writer->startDocument($version, $encoding);
        $writer->setIndent(true);
        $writer->setIndentString($indentString);

        // write the start of the document
        $writer->startElement($this->rootName);
        $rootAttributes = $rootContainer[$this->rootName];
        foreach ($rootAttributes[ '@attributes'] ?? [] as $name => $value) {
            $writer->writeAttribute($name, $value);
        }

        $this->writer = $writer;
        unset($writer);

        if ($headerContainer !== []) {
            $this->write($headerContainer);
        }

        if (count($itemContainerParts) > 1) {
            foreach ($itemContainerParts as $containerName) {
                // if last element we need to do something else
                if ($containerName === end($itemContainerParts)) {
                    // if this is the last container, we will use it as the item tag
                    $this->itemTagName = $containerName;
                } else {
                    $this->writer->startElement($containerName);
                }
            }
        } else {
            // if no item container, we use the root name as the item tag
            $this->itemTagName = $itemContainerParts[0];
        }
    }

    /**
     * Writes a single item under the current container.
     *
     * @param array $data Structured data for this item.
     */
    public function write(array $data): void
    {
        if ($this->itemTagName) {
            $this->writer->startElement($this->itemTagName);
        }

        $this->writeNode($data, $this->itemTagName);

        if ($this->itemTagName) {
            $this->writer->endElement();
        }
    }

    /**
     * Recursively writes array or scalar data as XML nodes.
     * Supports:
     * - `@attributes` for attributes
     * - `@value` or `@data` for text nodes
     * - Nested arrays for child elements
     * - Scalar values for repeated elements
     */
    private function writeNode(array|string|int|float $data, ?string $currentKey = null): void
    {
        // If scalar, write as element and return
        if (!is_array($data)) {
            if ($currentKey !== null) {
                $this->writer->writeElement($currentKey, (string)$data);
            }
            return;
        }

        // Handle split attribute nodes for associative arrays
        if (
            $this->splitAttributeNodes &&
            isset($data['@attributes']) && is_array($data['@attributes']) &&
            count($data['@attributes']) > 1 &&
            isset($data['@value']) && $currentKey
        ) {
            foreach ($data['@attributes'] as $attrName => $attrValue) {
                $this->writer->startElement($currentKey);
                $this->writer->writeAttribute($attrName, $attrValue);
                $this->writer->text($data['@value']);
                $this->writer->endElement();
            }
            return;
        }

        // Handle attributes
        if (isset($data['@attributes']) && is_array($data['@attributes'])) {
            foreach ($data['@attributes'] as $attrName => $attrValue) {
                $this->writer->writeAttribute($attrName, $attrValue);
            }
            unset($data['@attributes']);
        }

        // Handle simple text or CDATA
        if (isset($data['@value'])) {
            $this->writer->text($data['@value']);
            return;
        }
        if (isset($data['@CDATA'])) {
            $this->writer->writeCData($data['@CDATA']);
            return;
        }
        if (isset($data['@data'])) {
            $this->writer->text($data['@data']);
            return;
        }

        // Recursive children
        foreach ($data as $key => $value) {
            if (is_array($value) && array_keys($value) === range(0, count($value) - 1)) {
                // Numerically indexed array: write multiple elements with the same key
                foreach ($value as $item) {
                    $isAssociative = is_array($item) && array_keys($item) !== range(0, count($item) - 1);
                    if ($isAssociative) $this->writer->startElement($key);
                    $this->writeNode($item, $key);
                    if ($isAssociative) $this->writer->endElement();
                }
            } elseif (is_numeric($key) && is_array($value)) {
                // Numeric keys: inline collection (legacy support)
                $this->writeNode($value, $currentKey);
            } elseif (is_array($value)) {
                // Named child
                try {
                    $this->writer->startElement($key);
                } catch (\Error) {
                    dd($key, $value);
                }
                $this->writeNode($value, $key);
                $this->writer->endElement();
            } else {
                // Scalar element
                $this->writer->writeElement($key, (string)$value);
            }
        }
    }

    /**
     * Closes the current container element.
     */
    public function closeContainer(): void
    {
        if ($this->containerName) {
            if (isset($this->writer) && $this->writer instanceof \XMLWriter) {
                $this->writer->endElement();
            }
            $this->containerName = null;
            $this->itemTagName   = null;
        }
    }

    /**
     * Ends the document, flushing the buffer to the URI.
     */
    public function closeDocument(): void
    {
        if (isset($this->writer) && $this->writer instanceof \XMLWriter) {
            $this->writer->endDocument();
            $this->writer->flush();
            $this->writer = null;
        }
    }

    /**
     * Ensures the document is closed when destructed.
     */
    public function __destruct()
    {
        // Only attempt to close if writer is valid
        if (isset($this->writer) && $this->writer instanceof \XMLWriter) {
            $this->closeContainer();
            $this->closeDocument();
        }
    }

    public function close(): void
    {
        $this->closeDocument();
    }

    public function clear(): void
    {
        $this->closeDocument();
        file_put_contents($this->uri, '');
    }
}

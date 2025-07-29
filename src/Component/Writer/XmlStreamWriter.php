<?php

namespace Misery\Component\Writer;

use XMLWriter;

class XmlStreamWriter implements ItemWriterInterface
{
    private XMLWriter $writer;
    private string $uri;
    private string $rootName;
    private ?string $containerName = null;
    private ?string $itemTagName = null;

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
        $itemContainer = $options['item_container'] ?? null;
        if ($itemContainer === null) {
            throw new \InvalidArgumentException('Option "item_container" must be provided in options.');
        }
        $itemContainerParts = explode('|', $itemContainer);
        if ($this->rootName === $itemContainerParts[0]) {
            // If the item container is the same as the root, we don't need to start a new element
            unset($itemContainerParts[0]);
            $itemContainerParts = array_filter($itemContainerParts);
        }

        $version      = $options['version'] ?? '1.0';
        $encoding     = $options['encoding'] ?? 'UTF-8';
        $indent       = $options['indent'] ?? 2;
        $indentString = str_repeat($options['indent_string'] ?? ' ', $indent);

        $writer = new XMLWriter();
        $writer->openUri($uri);
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

        foreach ($itemContainerParts as $containerName) {
            $this->writer->startElement($containerName);
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

        $this->writeNode($data);

        if ($this->itemTagName) {
            $this->writer->endElement();
        }
    }

    /**
     * Recursively writes array data as XML nodes.
     * Supports:
     * - `@attributes` for attributes
     * - `@value` or `@data` for text nodes
     * - Nested arrays for child elements
     */
    private function writeNode(array $data): void
    {
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
            if (is_numeric($key) && is_array($value)) {
                // Numeric keys: inline collection
                $this->writeNode($value);

            } elseif (is_array($value)) {
                // Named child
                $this->writer->startElement($key);
                $this->writeNode($value);
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
            $this->writer->endElement();
            $this->containerName = null;
            $this->itemTagName   = null;
        }
    }

    /**
     * Ends the document, flushing the buffer to the URI.
     */
    public function closeDocument(): void
    {
        $this->writer->endDocument();
        $this->writer->flush();
    }

    /**
     * Ensures the document is closed when destructed.
     */
    public function __destruct()
    {
        $this->closeContainer();
        $this->closeDocument();
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

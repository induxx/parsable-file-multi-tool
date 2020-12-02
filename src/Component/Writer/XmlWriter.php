<?php

namespace Misery\Component\Writer;

class XmlWriter
{
    /** @var \SplFileObject */
    private $filename;

    private $header = [
        'version' => '1.0',
        'encoding' => 'UTF-8',
        'indent' => 4,
        'indent_string' => '    ',
    ];

    private $start = [
        'PutRequest' => [
            'attributes' => [
                'xmlns' => 'urn:xmlns:nedfox-retail3000api-com:putrequest',
                'version' => '6.0',
            ],
        ],
        'Records' => null,
    ];

    /**
     * @var \XMLWriter
     */
    private $writer;

    public function __construct(string $filename, array $header = [], array $start = [])
    {
        $this->filename = $filename;
        $this->start = array_merge($this->start, $start);
        $this->writer = $this->createWriter(array_merge($this->header, $header), $start);
    }

    private function createWriter(array $header, array $start = []): \XMLWriter
    {
        $XMLWriter = new \XMLWriter();
        $XMLWriter->openMemory();
        $XMLWriter->openUri($this->filename);
        $XMLWriter->startDocument($header['version'], $header['encoding']);
        $XMLWriter->setIndent($header['indent']);
        $XMLWriter->setIndentString($header['indent_string']);

        foreach ($start as $elemName => $elemAttributes) {
            $XMLWriter->startElement($elemName);
            if (is_array($elemAttributes)) {
                foreach ($elemAttributes as $attributeName => $attributeValue) {
                    $XMLWriter->writeAttribute($attributeName, $attributeValue);
                }
            }
        }

        return $XMLWriter;
    }

    public function write(array $data)
    {
        foreach($data as $key => $value) {
            if (\is_array($value)) {
                if (\is_string($key) && is_numeric(current(array_keys($value)))) {
                    foreach ($value as $i => $collectionValue) {
                        $this->writer->startElement($key);
                        $this->write($collectionValue);
                        $this->writer->endElement();
                    }
                    continue;
                }

                if (\is_string($key)) {
                    $this->writer->startElement($key);
                    $this->write($value);
                    $this->writer->endElement();
                    continue;
                }

                if (\is_numeric($key)) {
                    $this->write($value);
                }

                continue;
            } elseif (is_string($value)) {
                $this->writer->writeElement($key, $value);
                continue;
            }
        }
    }

    public function close(): void
    {
        $writer = $this->writer;

        foreach (count($this->start) as $elem) {
            $writer->endElement();
        }

        $writer->endDocument();

        $writer->flush();
    }

    public function __destruct()
    {
        $this->close();
    }
}


#### Action converting process
csv => parsed into an item 
item => parsed into and encodedItem
encodedItem => converted from the actions
convertedItem => decoded back into an item
item => is then saved as a csv

```php
$parser = \Misery\Component\Parser\CsvParser::create(__DIR__ . '/families.csv', ';');
$encoderFactory = new \Misery\Component\Encoder\ItemEncoderFactory();
$decoderFactory = new \Misery\Component\Decoder\ItemDecoderFactory();
$actionFactory = new \Misery\Component\Action\ItemActionProcessorFactory(
    new \Misery\Component\Common\Registry\Registry('action')
);

$encoder = $encoderFactory->createItemEncoder($config = []);
$decoder = $decoderFactory->createItemDecoder($config = []);
$actionProcessor = $actionFactory->createActionProcessor(
    new \Misery\Component\Source\SourceCollection('akeneo'),
    ['my actions']
);

$writer = new \Misery\Component\Writer\CsvWriter('somewhere.csv');

// iterate data
foreach ($parser->getIterator() as $row) {
    $encodedData = $encoder->encode($row);
    $convertedData = $actionProcessor->process($encodedData);
    $decodedData = $decoder->decode($convertedData);

    $writer->write($decodedData);
}
```
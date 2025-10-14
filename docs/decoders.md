# Decoder

Decoding is the reverse of encoding.
Some encoders options can be destructive, meaning we can't revert those changes.
So be careful which modifiers you connect during encoding.

> # encoding
> Csv => structured item
> # decoding
> structured item => CSV

# Examples

```php
$parser = \Misery\Component\Parser\CsvParser::create(__DIR__ . '/families.csv');
$decoderFactory = new \Misery\Component\Decoder\ItemDecoderFactory();
$decoderFactory->addRegistry($formatRegistry);
$decoderFactory->addRegistry($modifierRegistry);

$decoder = $decoderFactory->createItemDecoder(
    \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . 'Blueprint/akeneo/csv/families.yaml')
);

foreach ($parser->getIterator() as $row) {
    $decodedData = $decoder->decode($row);
}
```

This could be a valid families.yaml

```yaml
encode:
  code:
    string: ~
  attributes:
    list: ~
parse:
  unflatten:
    separator: '-'
  nullify: ~
  format:
    delimiter: ';'
    enclosure: '"'
    reference: 'code'
```

Before decoding

```json
{
   "code": "auto",
   "label": {
      "en_US": "auto",
      "nl_BE": "car"
    },
   "attributes": ["description","maximum_frame_rate","maximum_video_resolution"]
}
```

After decoding

```json
{
   "code": "auto",
   "label-en_US": "auto",
   "label-nl_BE": "car",
   "attributes": "description,maximum_frame_rate,maximum_video_resolution"
}
```
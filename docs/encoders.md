# Encoders

# Examples
```php
$parser = \Misery\Component\Parser\CsvParser::create(__DIR__ . '/families.csv', ';');
$encoderFactory = new \Misery\Component\Encoder\ItemEncoderFactory();
$encoderFactory->addRegistry($formatRegistry);
$encoderFactory->addRegistry($modifierRegistry);

$encoder = $encoderFactory->createItemEncoder(
    \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . 'Blueprint/akeneo/csv/families.yaml')
);

// iterate data
foreach ($parser->getIterator() as $row) {
    $encodedData = $encoder->encode($row);
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

Before Encoding

```json
{
   "code": "auto",
   "label-en_US": "auto",
   "label-nl_BE": "car",
   "attributes": "description,maximum_frame_rate,maximum_video_resolution"
}
```

After Encoding 

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

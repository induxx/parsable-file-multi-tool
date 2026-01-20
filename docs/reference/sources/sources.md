# Sources 

Sources are a way of making wrapping similar datasets.
For some parts like ActionProcessor and ValidationProcessor we need to have external source to fetch data.

```php

$source = new \Misery\Component\Source\Source(
    SourceType::file(),
    new ItemEncoder([]),
    new ItemDecoder([]),
    $filePath,
    'product'
);

$sourceCollection = new \Misery\Component\Source\SourceCollection('akeneo/csv');
$sourceCollection->add($source);

$source = $sourceCollection->get('product');

```


```php

$references = [
    'products' => 'path/to/products.csv',
    'attributes' => 'path/to/attributes.csv',
];

$sourceCollection = \Misery\Component\Source\SourceCollectionFactory::create(
    $itemEncoderFactory, 
    $itemDecoderFactory, 
    $references, 
    $bluePrintDir = 'akeneo/csv'
);

```

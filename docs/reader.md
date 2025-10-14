# Reader

## Reader Interface

```php
interface Reader
{
    public function read(): \Iterator;
    public function getIterator(): \Iterator;
    public function find(array $constraints): self;
    public function filter(callable $callable): self;
    public function map(callable $callable): self;
    public function getItems(): array;
}
```

## RowReader Interface
```php
interface ItemReader
{
    public function index(array $lines): self;
}
```

## examples

Here we get id and name from user 'Nick'

```php
$parser = Misery\Component\Parser\CsvParser::create(__DIR__ . '/users.csv');
$reader = new Misery\Component\Reader\ItemReader($parser);

$reader
    ->find(['name' => 'Nick'])
;
\Misery\Component\Filter\ColumnReducer::reduce($reader, 'id', 'name')->getItems();
```

Here we get id and name from first 100 lines

```php
$parser = Misery\Component\Parser\CsvParser::create(__DIR__ . '/users.csv');
$reader = new Misery\Component\Reader\ItemReader($parser);

$reader
    ->index(range(1, 100))
;
\Misery\Component\Filter\ColumnReducer::reduce($reader, 'first_name', 'last_name')->getItems();

```

Here we get id and name from user 'Nick' using the filter method

```php
$parser = Misery\Component\Parser\CsvParser::create(__DIR__ . '/users.csv');
$reader = new Misery\Component\Reader\ItemReader($parser);

$reader
    ->filter(function ($row) {
        return $row['first_name'] === 'Nick';
    })
;
\Misery\Component\Filter\ColumnReducer::reduce($reader, 'first_name', 'last_name')->getItems();
```

In some case we just want to read the column values of a specified column name.
Specify this 

```php
$parser = Misery\Component\Parser\CsvParser::create(__DIR__ . '/users.csv');
$reader = new Misery\Component\Reader\ItemReader($parser);

# using the map function
$items = $reader
    ->map(function(array $row) {
         return $row['sku'];
     })
    ->getItems()
;

$items = \Misery\Component\Item\Builder\ReferenceBuilder::buildValues($reader, 'sku');

$items = [
  'A',
  'B',
  'C',
];


```

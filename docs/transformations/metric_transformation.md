# Transformations 

# UNDER CONSTRUCTION

Transform a metric

for example 
METER => M

```php

$weight = [
    'amount' => 1.000,
    'unit' => 'METER',
];

// could become

$weight = [
    'amount' => 1,
    'unit' => 'M',
];

```

for this to would you will need supply the following

```yaml
  metric_mapping:
    encode:
      amount: float
        decimal: 0
      unit: mapping
    mappings:
      METER: M
      CENTIMETER: CM
    format: '%amount% %unit'
```
Becomes
```php
$weight = [
    '1 M',
];
```

```yaml
  metric_mapping:
    encode:
      amount: float
        decimal: 0
      unit: mapping
    mappings:
      METER: M
      CENTIMETER: CM
```

Becomes
```php
$weight = [
    'amount' => 1,
    'unit' => 'M',
];
```
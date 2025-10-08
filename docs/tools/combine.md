# Combine Tool

## Overview

The Combine tool merges multiple data sources or files into a single unified dataset. This tool enables data consolidation from various sources while maintaining data integrity and providing flexible combination strategies for different data structures and formats.

## Syntax

```php
// PHP API usage
$combiner = new Combiner();
$result = $combiner->combine($source1, $source2, $options);
```

```yaml
# YAML configuration usage
pipeline:
  input:
    reader:
      type: combine
      sources:
        - file1.csv
        - file2.csv
      strategy: merge|append|union
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| sources | array | Yes | - | List of data sources to combine |
| strategy | string | No | merge | Combination strategy (merge, append, union) |
| key_field | string | No | - | Field to use for merge operations |
| options | object | No | {} | Additional combination options |

## Examples

### Basic File Combination

```yaml
# Combine multiple CSV files
pipeline:
  input:
    reader:
      type: combine
      sources:
        - products_2023.csv
        - products_2024.csv
      strategy: append
  output:
    writer:
      type: csv
      filename: combined_products.csv
```

### Merge with Key Field

```yaml
# Merge files based on common key
pipeline:
  input:
    reader:
      type: combine
      sources:
        - customer_base.csv
        - customer_updates.csv
      strategy: merge
      key_field: customer_id
  output:
    writer:
      type: csv
      filename: merged_customers.csv
```

### PHP API Usage

```php
use Misery\Component\Common\Tool\Combiner;

// Combine multiple parsers
$parser1 = CsvParser::create(__DIR__ . '/file1.csv');
$parser2 = CsvParser::create(__DIR__ . '/file2.csv');
$parser3 = CsvParser::create(__DIR__ . '/file3.csv');

$combiner = new Combiner();
$combined = $combiner->combine([$parser1, $parser2, $parser3], [
    'strategy' => 'append',
    'preserve_order' => true
]);

// Process combined data
foreach ($combined as $row) {
    // Process each row from all sources
    echo $row['name'] . "\n";
}
```

## Combination Strategies

### Append Strategy
Concatenates all data sources sequentially, preserving original order within each source.

```yaml
strategy: append
# Result: [source1_data..., source2_data..., source3_data...]
```

### Merge Strategy
Combines data based on a key field, with later sources overriding earlier ones for duplicate keys.

```yaml
strategy: merge
key_field: id
# Result: Unified dataset with unique keys, latest values preserved
```

### Union Strategy
Combines all unique records from all sources, removing duplicates based on all fields.

```yaml
strategy: union
# Result: Unique records from all sources combined
```

## Use Cases

### Use Case 1: Historical Data Consolidation
Combine data files from different time periods into a single comprehensive dataset.

### Use Case 2: Multi-Source Data Integration
Merge data from different systems or departments that share common identifiers.

### Use Case 3: Incremental Data Updates
Apply updates from multiple sources to a base dataset using merge strategies.

## Configuration Options

### preserve_order
- **Type**: boolean
- **Default**: true
- **Description**: Maintains original order within each source during combination

### duplicate_handling
- **Type**: string
- **Options**: first, last, error
- **Default**: last
- **Description**: How to handle duplicate keys in merge operations

### field_mapping
- **Type**: object
- **Description**: Map field names between different sources before combination

## Common Issues and Solutions

### Issue: Schema Mismatch

**Symptoms:** Errors during combination due to different field structures.

**Cause:** Sources have different field names or structures.

**Solution:** Use field mapping to normalize schemas before combination.

```yaml
pipeline:
  input:
    reader:
      type: combine
      sources:
        - legacy_data.csv
        - new_data.csv
      field_mapping:
        legacy_data.csv:
          old_customer_id: customer_id
          old_name: customer_name
```

### Issue: Memory Usage with Large Files

**Symptoms:** High memory consumption or out-of-memory errors.

**Cause:** Loading all sources into memory simultaneously.

**Solution:** Use streaming combination for large datasets.

```php
$combiner = new Combiner();
$combiner->setStreamingMode(true);
$result = $combiner->combine($sources, $options);
```

### Issue: Duplicate Key Conflicts

**Symptoms:** Unexpected data loss or overwrites during merge operations.

**Cause:** Multiple sources contain the same keys with different values.

**Solution:** Configure duplicate handling strategy explicitly.

```yaml
strategy: merge
key_field: id
duplicate_handling: first  # Keep first occurrence, ignore later ones
```

## Best Practices

- Validate that all sources have compatible schemas before combination
- Use appropriate combination strategy based on your data requirements
- Test combination logic with sample data before processing large datasets
- Consider memory implications when combining many large sources
- Document the expected behavior for duplicate keys and conflicts
- Use field mapping to normalize different source schemas

## Integration Guidelines

### With Pipeline Processing
- Use combine tool at the input stage before applying transformations
- Apply data cleaning and validation after combination
- Consider the order of sources when using append strategy

### With External Systems
- Ensure all source files are accessible and readable
- Handle missing or corrupted source files gracefully
- Implement proper error handling for combination failures

## Related Components

- [Pipeline Directive](../directives/pipelines.md) - Integration with data processing workflows
- [Data Sources](../data_source/reader.md) - Input configuration options
- [Compare Tool](./compare.md) - For analyzing differences between sources

## See Also

- [Data Transformation Guide](../user-guide/transformations.md)
- [File Processing](../user-guide/transformations.md)
- [Multi-Source Integration](../user-guide/transformations.md)

---

*Last updated: 2024-12-19*
*Category: reference*
*Tool Type: data-combination*
# Compare Tool

## Overview

The Compare tool analyzes differences between two data sources, identifying added, modified, and removed records. This tool is essential for data synchronization, change detection, and audit processes, working with all parsable file formats to provide detailed comparison results.

## Syntax

```php
// PHP API usage
$compare = new ItemCompare($sourceA, $sourceB);
$result = $compare->compare('reference_field');
```

```yaml
# YAML configuration usage (via transformation command)
pipeline:
  input:
    reader:
      type: compare
      sources:
        old: old_data.csv
        new: new_data.csv
      reference_fields: [id]
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| sources | object | Yes | - | Old and new data sources to compare |
| reference_fields | array | Yes | - | Fields that uniquely identify records |
| comparison_mode | string | No | full | Comparison depth (full, keys_only, values_only) |
| ignore_fields | array | No | [] | Fields to exclude from comparison |

## Examples

### Basic File Comparison

```php
use Misery\Component\Csv\Compare\ItemCompare;
use Misery\Component\Parser\CsvParser;
use Misery\Component\Common\Cursor\CachedCursor;

// Load data sources
$oldData = CsvParser::create(__DIR__ . '/products_old.csv');
$newData = CachedCursor::create(CsvParser::create(__DIR__ . '/products_new.csv'));

// Create comparison
$compare = new ItemCompare($oldData, $newData);

// Compare using SKU as reference
$result = $compare->compare('sku');

// Process results
foreach ($result as $change) {
    echo "Change type: " . $change['type'] . "\n";
    echo "Reference: " . $change['reference'] . "\n";
    if ($change['type'] === 'modified') {
        echo "Before: " . json_encode($change['before']) . "\n";
        echo "After: " . json_encode($change['after']) . "\n";
    }
}
```

### Multi-Field Reference Comparison

```php
// Compare using composite key (multiple reference fields)
$compare = new ItemCompare($sourceA, $sourceB);
$result = $compare->compare(['customer_id', 'order_date']);

// Results will use combined reference for uniqueness
```

### Comparison with Field Exclusions

```php
// Ignore timestamp fields during comparison
$compare = new ItemCompare($oldData, $newData);
$compare->setIgnoreFields(['created_at', 'updated_at', 'last_sync']);
$result = $compare->compare('id');
```

## Comparison Results

### Result Structure
```php
[
    [
        'type' => 'added',           // Change type: added, removed, modified
        'reference' => 'PROD_001',   // Reference field value(s)
        'data' => [...],             // New record data (for added)
    ],
    [
        'type' => 'removed',
        'reference' => 'PROD_002',
        'data' => [...],             // Old record data (for removed)
    ],
    [
        'type' => 'modified',
        'reference' => 'PROD_003',
        'before' => [...],           // Original record data
        'after' => [...],            // Updated record data
        'changes' => [               // Specific field changes
            'price' => ['old' => '99.99', 'new' => '89.99'],
            'status' => ['old' => 'active', 'new' => 'discontinued']
        ]
    ]
]
```

### Change Types

#### Added Records
Records that exist in the new source but not in the old source.

#### Removed Records
Records that exist in the old source but not in the new source.

#### Modified Records
Records that exist in both sources but have different field values.

## Use Cases

### Use Case 1: Data Synchronization
Identify changes between systems to synchronize data updates efficiently.

### Use Case 2: Audit and Compliance
Track data changes for audit trails and compliance reporting.

### Use Case 3: Quality Assurance
Validate data migration or transformation results by comparing before and after states.

## Configuration Options

### comparison_mode
- **full**: Compare all fields for differences
- **keys_only**: Only check for added/removed records
- **values_only**: Only compare field values, ignore structural changes

### ignore_fields
Array of field names to exclude from comparison (useful for timestamps, auto-generated IDs).

### case_sensitive
Boolean flag to control case-sensitive string comparisons.

## Common Issues and Solutions

### Issue: Performance with Large Datasets

**Symptoms:** Slow comparison or high memory usage with large files.

**Cause:** Loading entire datasets into memory for comparison.

**Solution:** Use cached cursors and streaming comparison techniques.

```php
// Use cached cursor for better performance
$sourceB = CachedCursor::create(CsvParser::create(__DIR__ . '/large_file.csv'));
$compare = new ItemCompare($sourceA, $sourceB);
```

### Issue: Composite Key Handling

**Symptoms:** Incorrect matching when records should be unique based on multiple fields.

**Cause:** Using single field reference when composite key is needed.

**Solution:** Specify multiple reference fields for composite keys.

```php
// Use multiple fields for unique identification
$result = $compare->compare(['customer_id', 'product_id', 'date']);
```

### Issue: False Positives from Timestamps

**Symptoms:** All records showing as modified due to timestamp differences.

**Cause:** Timestamp fields being included in comparison.

**Solution:** Exclude timestamp fields from comparison.

```php
$compare->setIgnoreFields(['created_at', 'updated_at', 'modified_date']);
```

## Best Practices

- Choose appropriate reference fields that uniquely identify records
- Use composite keys when single fields don't provide uniqueness
- Exclude auto-generated or timestamp fields from comparison
- Test comparison logic with sample data before processing large datasets
- Consider performance implications with large datasets
- Document the business logic for what constitutes a "change"
- Validate reference field consistency between sources

## Integration Guidelines

### With Data Processing Pipelines
- Use comparison results to drive conditional processing
- Apply different actions based on change types (added, modified, removed)
- Log comparison results for audit and monitoring

### With External Systems
- Use comparison results to optimize API calls (only update changed records)
- Implement incremental synchronization based on comparison output
- Handle comparison failures gracefully in automated processes

## Related Components

- [Combine Tool](./combine.md) - For merging data sources
- [Copy Tool](./copy.md) - For data replication operations
- [Pipeline Directive](../directives/pipelines.md) - Integration with processing workflows

## See Also

- [Data Transformation Guide](../user-guide/transformations.md)
- [File Processing](../user-guide/transformations.md)
- [Data Synchronization Patterns](../user-guide/transformations.md)

---

*Last updated: 2024-12-19*
*Category: reference*
*Tool Type: data-comparison*

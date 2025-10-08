# Copy Tool

## Overview

The Copy tool provides flexible data replication capabilities by connecting readers and writers with optional transformations in between. Rather than a dedicated copy utility, the parsable-file-multi-tool uses a composable approach where readers and writers are connected directly, enabling format conversion and selective copying during the process.

## Syntax

```php
// PHP API usage - Basic copy
$parser = CsvParser::create($sourceFile);
$writer = new CsvWriter($destinationFile);
$parser->loop(function ($row) use ($writer) {
    $writer->write($row);
});
```

```yaml
# YAML configuration usage
pipeline:
  input:
    reader:
      type: csv
      filename: source.csv
  output:
    writer:
      type: csv
      filename: destination.csv
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| source | string/object | Yes | - | Source data file or parser |
| destination | string/object | Yes | - | Destination file or writer |
| format | string | No | same | Output format (csv, json, xml, xlsx) |
| filters | array | No | [] | Conditions for selective copying |

## Examples

### Basic File Copy

```php
// Copy CSV file as-is
$parser = CsvParser::create(__DIR__ . '/family.csv');
$csvWriter = new CsvWriter(__DIR__ . '/family_copy.csv');

$parser->loop(function ($row) use ($csvWriter) {
    $csvWriter->write($row);
});
```

### Format Conversion Copy

```php
// Copy CSV to XML format
$parser = CsvParser::create(__DIR__ . '/family.csv');
$xmlWriter = new XmlWriter(__DIR__ . '/family_copy.xml');

$parser->loop(function ($row) use ($xmlWriter) {
    $xmlWriter->write($row);
});
```

### Selective Copy with Filtering

```php
use Misery\Component\Reader\Reader;

// Copy only specific records
$parser = CsvParser::create(__DIR__ . '/family.csv');
$reader = new Reader($parser);
$csvWriter = new CsvWriter(__DIR__ . '/family_led_tvs.csv');

$reader
    ->find(['family' => 'led_tvs', 'display_diagonal' => '26'])
    ->loop(function ($row) use ($csvWriter) {
        $csvWriter->write($row);
    });
```

### Pipeline-Based Copy

```yaml
# Simple file copy via pipeline
pipeline:
  input:
    reader:
      type: csv
      filename: source_data.csv
  output:
    writer:
      type: csv
      filename: copied_data.csv
```

### Copy with Transformation

```yaml
# Copy with data transformation
pipeline:
  input:
    reader:
      type: csv
      filename: source_data.csv
  actions:
    clean_data:
      action: retain
      keys: [id, name, email]
    format_names:
      action: format
      field: name
      functions: [trim, uppercase]
  output:
    writer:
      type: csv
      filename: cleaned_copy.csv
```

## Copy Strategies

### Direct Copy
Replicate data exactly as-is without modifications.

```php
$parser->loop(function ($row) use ($writer) {
    $writer->write($row);
});
```

### Format Conversion
Change file format during copy operation.

```php
// CSV to JSON conversion
$csvParser = CsvParser::create('input.csv');
$jsonWriter = new JsonWriter('output.json');
$csvParser->loop(function ($row) use ($jsonWriter) {
    $jsonWriter->write($row);
});
```

### Selective Copy
Copy only records that match specific criteria.

```php
$reader->find($criteria)->loop(function ($row) use ($writer) {
    $writer->write($row);
});
```

## Use Cases

### Use Case 1: Backup and Archival
Create backup copies of data files for archival or disaster recovery purposes.

### Use Case 2: Format Migration
Convert data between different formats while preserving content integrity.

### Use Case 3: Data Subset Extraction
Extract specific subsets of data based on filtering criteria.

## Supported Formats

### Input Formats
- CSV (Comma-separated values)
- JSON (JavaScript Object Notation)
- XML (Extensible Markup Language)
- XLSX (Excel spreadsheet)
- JSONL (JSON Lines)

### Output Formats
- CSV (Comma-separated values)
- JSON (JavaScript Object Notation)
- XML (Extensible Markup Language)
- XLSX (Excel spreadsheet)
- JSONL (JSON Lines)

## Common Issues and Solutions

### Issue: Memory Usage with Large Files

**Symptoms:** High memory consumption or out-of-memory errors during copy operations.

**Cause:** Loading entire file into memory instead of streaming.

**Solution:** Use streaming copy with loop processing.

```php
// Streaming copy for large files
$parser = CsvParser::create($largeFile);
$parser->loop(function ($row) use ($writer) {
    $writer->write($row);
    // Memory is freed after each row
});
```

### Issue: Format Compatibility

**Symptoms:** Data loss or corruption during format conversion.

**Cause:** Incompatible data structures between source and destination formats.

**Solution:** Use appropriate transformations during copy process.

```yaml
pipeline:
  input:
    reader:
      type: xml
      filename: complex_data.xml
  actions:
    flatten_structure:
      action: expand
      field: nested_data
  output:
    writer:
      type: csv
      filename: flattened_data.csv
```

### Issue: Partial Copy Failures

**Symptoms:** Copy operation stops partway through large datasets.

**Cause:** Errors in individual records or resource constraints.

**Solution:** Implement error handling and resume capabilities.

```php
$parser->loop(function ($row) use ($writer) {
    try {
        $writer->write($row);
    } catch (Exception $e) {
        // Log error and continue
        error_log("Failed to copy row: " . json_encode($row));
    }
});
```

## Best Practices

- Use streaming copy for large files to minimize memory usage
- Validate source file integrity before starting copy operations
- Choose appropriate output format based on downstream requirements
- Implement error handling for robust copy operations
- Test format conversions with sample data before processing large files
- Consider performance implications of complex transformations during copy
- Document the purpose and requirements of each copy operation

## Integration Guidelines

### With Pipeline Processing
- Use copy operations as the foundation for more complex transformations
- Combine copy with filtering and transformation actions
- Leverage pipeline configuration for repeatable copy processes

### With External Systems
- Ensure proper file permissions for source and destination locations
- Handle network interruptions for remote file operations
- Implement retry logic for failed copy operations

## Related Components

- [Compare Tool](./compare.md) - For analyzing differences between copied files
- [Combine Tool](./combine.md) - For merging multiple sources during copy
- [Pipeline Directive](../directives/pipelines.md) - Configuration-based copy operations

## See Also

- [Data Transformation Guide](../../../user-guide/transformations.md)
- [File Format Support](../../../data_source/reader.md)
- [Data Processing Patterns](../../../user-guide/transformations.md)

---

*Last updated: 2024-12-19*
*Category: reference*
*Tool Type: data-replication*
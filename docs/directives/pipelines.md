# Pipeline Directive

## Overview

The pipeline directive defines a series of processing steps that transform data as it flows from input to output. Pipelines provide a flexible and powerful framework for data transformation, filtering, and manipulation, enabling complex data processing workflows through a structured three-stage approach: input, actions, and output.

## Syntax

```yaml
pipeline:
  input:
    reader:
      type: reader_type
      filename: input_file
      # Additional input configuration
  actions:
    action_name:
      action: action_type
      # Action-specific parameters
  output:
    writer:
      type: writer_type
      filename: output_file
      # Additional output configuration
```

## Configuration Options

| Section | Type | Required | Default | Description |
|---------|------|----------|---------|-------------|
| input | object | Yes | - | Data source configuration |
| actions | object | No | {} | Processing steps to apply to data |
| output | object | Yes | - | Data destination configuration |

### Configuration Details

#### input
Defines the data source for the pipeline:
- **reader**: Configuration for data input
- **type**: Input format (csv, json, jsonl, xlsx, list, http, etc.)
- **filename**: Source file path or alias reference
- **converter**: Optional data converter for complex formats

#### actions
Sequential processing steps applied to data:
- Each action has a unique name and configuration
- Actions are executed in the order they appear
- Can include filtering, transformation, formatting, and validation steps

#### output
Defines the data destination:
- **writer**: Configuration for data output
- **type**: Output format (csv, json, jsonl, xlsx, buffer_csv, http, etc.)
- **filename**: Destination file path or alias reference
- **converter**: Optional data converter for complex formats

## Examples

### Basic Data Transformation Pipeline

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: customer_data.csv
  actions:
    retain_important_fields:
      action: retain
      keys: [name, age, gender]
    rename_name_field:
      action: rename
      from: name
      to: customer_name
    format_age_field:
      action: format
      field: age
      functions: [number]
      format: '%02d'
  output:
    writer:
      type: csv
      filename: processed_customer_data.csv
```

### Format Conversion Pipeline

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: customer_data.csv
  output:
    writer:
      type: jsonl
      filename: customer_data_reformatted.jsonl
```

### API Integration Pipeline

```yaml
pipeline:
  input:
    http:
      type: rest_api
      account: '%api_connection%'
      endpoint: products
      method: GET
      converter: 'akeneo/product/api'
  actions:
    filter_active:
      action: statement
      when:
        field: enabled
        operator: EQUALS
        value: true
    format_prices:
      action: format
      field: price
      functions: [number]
      format: '%.2f'
  output:
    writer:
      type: csv
      filename: active_products.csv
      converter: 'akeneo/product/csv'
```

### List-Based Processing Pipeline

```yaml
pipeline:
  input:
    reader:
      type: list
      list: product_attributes
  actions:
    uppercase_codes:
      action: format
      field: code
      functions: [uppercase]
    add_prefix:
      action: format
      field: code
      format: 'ATTR_%s'
  output:
    writer:
      type: csv
      filename: formatted_attributes.csv
```

## Use Cases

### Use Case 1: Data Cleaning and Standardization
Transform raw data into clean, standardized formats suitable for further processing or analysis.

### Use Case 2: Format Conversion
Convert data between different formats (CSV ↔ JSON, API ↔ Files) while preserving data integrity.

### Use Case 3: Data Integration
Combine data from multiple sources and transform it into a unified format for downstream systems.

## Behavior and Processing

### Processing Order
1. **Input Stage**: Data is read from the specified source
2. **Action Stage**: Each action is applied sequentially to transform the data
3. **Output Stage**: Processed data is written to the specified destination

### Data Flow
Data flows through the pipeline as a stream of records, with each action potentially modifying the structure or content of individual records.

### Error Handling
- Pipeline execution stops on the first error encountered
- Failed records can be logged or written to error files depending on configuration
- Some actions support error tolerance and continuation

## Common Patterns

### Pattern 1: ETL (Extract, Transform, Load)
```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: raw_data.csv
  actions:
    clean_data:
      action: retain
      keys: [id, name, email, status]
    validate_email:
      action: statement
      when:
        field: email
        operator: REGEX
        value: '^[^@]+@[^@]+\.[^@]+$'$'$'$'$'
  output:
    writer:
      type: csv
      filename: clean_data.csv
```

### Pattern 2: Data Enrichment
```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: base_data.csv
  actions:
    add_lookup_data:
      action: key_mapping
      list: enrichment_data
    calculate_derived_fields:
      action: copy
      from: base_price
      to: discounted_price
  output:
    writer:
      type: csv
      filename: enriched_data.csv
```

## Input Types

### File-Based Inputs
- **csv**: Comma-separated values
- **json**: JSON format
- **jsonl**: JSON Lines format
- **xlsx**: Excel spreadsheet
- **yaml**: YAML format

### API-Based Inputs
- **http**: REST API endpoints
- **rest_api**: Specialized REST API reader

### Special Inputs
- **list**: Process predefined list data
- **buffer**: In-memory data processing

## Output Types

### File-Based Outputs
- **csv**: Comma-separated values
- **json**: JSON format
- **jsonl**: JSON Lines format
- **xlsx**: Excel spreadsheet
- **buffer_csv**: In-memory CSV processing

### API-Based Outputs
- **http**: REST API endpoints
- **rest_api**: Specialized REST API writer

## Common Issues and Solutions

### Issue: Input File Not Found

**Symptoms:** Pipeline fails to start with file not found errors.

**Cause:** Incorrect file path or missing source file.

**Solution:** Verify file paths and ensure source files exist.

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: '%workpath%/data/source.csv'  # Use context variables
```

### Issue: Action Order Dependencies

**Symptoms:** Actions fail because required fields don't exist or have wrong format.

**Cause:** Actions are not ordered correctly based on their dependencies.

**Solution:** Order actions so that prerequisite transformations occur first.

```yaml
actions:
  # First: ensure field exists
  copy_field:
    action: copy
    from: original_field
    to: working_field
  # Then: transform the field
  format_field:
    action: format
    field: working_field
    functions: [uppercase]
```

### Issue: Memory Issues with Large Files

**Symptoms:** Pipeline runs out of memory or becomes very slow.

**Cause:** Processing very large files without streaming optimization.

**Solution:** Use streaming-compatible actions and consider file splitting.

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: large_file.csv
      options:
        streaming: true  # Enable streaming mode
```

## Best Practices

- Design pipelines with clear, single-purpose actions
- Use descriptive names for actions that indicate their purpose
- Order actions logically based on data dependencies
- Test pipelines with sample data before processing large datasets
- Use appropriate input/output types for your data formats
- Implement error handling and validation where appropriate
- Document complex transformation logic
- Consider performance implications for large datasets

## Related Directives

- [Context](./context.md) - For defining variables used in pipelines
- [Aliases](./aliases.md) - For file path references
- [List](./list.md) - For list-based input sources
- [Converters](./converters.md) - For data format transformation

## See Also

- [Directive Overview](../directives.md)
- [Data Sources](../data_source/reader.md)
- [Data Writers](../data_source/writer.md)
- [Actions Reference](../actions/)
- [Transformation Guide](../user-guide/transformations.md)

---

*Last updated: 2024-12-19*
*Category: reference*
*Directive Type: processing*

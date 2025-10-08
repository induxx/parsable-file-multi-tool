# List Directive

## Overview

The list directive defines collections of multi-dimensional data that can be referenced throughout transformation configurations. Unlike the mapping directive which handles simple key-value pairs, lists can store complex, structured data arrays that are useful for lookup operations, filtering, and data processing in pipelines.

## Syntax

```yaml
list:
  - name: list_identifier
    values:
      - item1
      - item2
    # OR for dynamic lists
  - name: dynamic_list_identifier
    source: data_source.csv
    source_command: command_type
    options:
      key: field_name
      value: field_name
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| name | string | Yes | - | Unique identifier for the list |
| values | array | No* | - | Static array of values for the list |
| source | string | No* | - | External data source file for dynamic lists |
| source_command | string | No* | - | Command to process source data |
| options | object | No | {} | Configuration options for source commands |

*Either `values` or `source` with `source_command` is required.

### Configuration Details

#### name
- Unique identifier used to reference the list throughout the configuration
- Must be a valid YAML key
- Case-sensitive identifier

#### values (Static Lists)
- Array of static values or objects
- Can contain simple values or complex nested structures
- Defined directly in the configuration file

#### source (Dynamic Lists)
- Path to external data source file
- Supports CSV, JSON, and other readable formats
- File path can use aliases and context variables

#### source_command
Available commands for processing source data:
- `key_value_pair` - Creates key-value mappings from source data
- `filter` - Filters source data based on criteria

#### options
Configuration specific to the source_command:
- For `key_value_pair`: `key` and `value` field specifications
- For `filter`: `criteria` and `return_value` specifications

## Examples

### Static List Definition

```yaml
# Define a static list of attributes
list:
  - name: product_attributes
    values:
      - code: sku
        type: text
      - code: name
        type: text
      - code: price
        type: number
      - code: weight
        type: metric
        unit: kg
```

### Dynamic Key-Value List

```yaml
# Create mapping from external data source
list:
  - name: customer_id_to_username_mapping
    source: customer_info.csv
    source_command: key_value_pair
    options:
      key: customer_id
      value: username
```

### Dynamic Filtered List

```yaml
# Filter active customers from source data
list:
  - name: active_customer_ids
    source: customer_data.csv
    source_command: filter
    options:
      criteria:
        is_active: '1'
        status: 'verified'
      return_value: customer_id
```

### Using Lists in Pipelines

```yaml
# Static list as input source
pipeline:
  input:
    reader:
      type: list
      list: product_attributes
  actions:
    process_attributes:
      action: format
      field: code
      functions: [uppercase]
  output:
    writer:
      type: csv
      filename: processed_attributes.csv
```

### List-Based Data Mapping

```yaml
# Use dynamic list for field mapping
list:
  - name: field_mappings
    source: mapping_config.csv
    source_command: key_value_pair
    options:
      key: old_field
      value: new_field

pipeline:
  input:
    reader:
      type: csv
      filename: source_data.csv
  actions:
    apply_mappings:
      action: key_mapping
      list: field_mappings
  output:
    writer:
      type: csv
      filename: mapped_data.csv
```

## Use Cases

### Use Case 1: Reference Data Management
Store lookup tables and reference data that can be used across multiple transformation steps.

### Use Case 2: Dynamic Configuration
Create lists from external sources that can change without modifying the main configuration.

### Use Case 3: Data Filtering and Validation
Use filtered lists to validate data or restrict processing to specific subsets.

## Behavior and Processing

### Processing Order
Lists are resolved during configuration parsing, before pipeline execution begins.

### Data Flow
Lists serve as reference data and don't directly participate in the main data flow unless used as input sources.

### Variable Scope
Lists are globally available throughout the configuration and can be referenced by name in any directive.

## Common Patterns

### Pattern 1: Lookup Table
```yaml
list:
  - name: status_codes
    values:
      - code: 'A'
        description: 'Active'
      - code: 'I'
        description: 'Inactive'
      - code: 'P'
        description: 'Pending'
```

### Pattern 2: Dynamic Mapping
```yaml
list:
  - name: category_mappings
    source: category_lookup.csv
    source_command: key_value_pair
    options:
      key: old_category
      value: new_category
```

## Source Commands

### key_value_pair Command
Creates key-value mappings from source data:

```yaml
list:
  - name: mapping_list
    source: data.csv
    source_command: key_value_pair
    options:
      key: source_field    # Field to use as key
      value: target_field  # Field to use as value
```

### filter Command
Filters source data based on criteria:

```yaml
list:
  - name: filtered_list
    source: data.csv
    source_command: filter
    options:
      criteria:
        field1: 'value1'
        field2: 'value2'
      return_value: result_field
```

## Common Issues and Solutions

### Issue: List Not Found

**Symptoms:** Error messages about undefined list references.

**Cause:** Referencing a list name that wasn't defined or has a typo.

**Solution:** Ensure list names match exactly between definition and usage.

```yaml
# Correct list definition and reference
list:
  - name: my_lookup_table  # Definition

pipeline:
  actions:
    map_values:
      action: key_mapping
      list: my_lookup_table  # Must match exactly
```

### Issue: Source File Not Found

**Symptoms:** Errors during list processing about missing source files.

**Cause:** Source file path is incorrect or file doesn't exist.

**Solution:** Verify file paths and ensure files exist.

```yaml
# Use proper file paths
list:
  - name: dynamic_data
    source: '%workpath%/lookup_data.csv'  # Use context variables
    source_command: key_value_pair
    options:
      key: id
      value: name
```

## Best Practices

- Use descriptive names that clearly indicate the list's purpose
- For large datasets, prefer dynamic lists over static values
- Validate source files exist before running transformations
- Document the structure and purpose of complex list data
- Use consistent field naming in source data for key_value_pair commands
- Consider performance implications of large lists in memory

## Related Directives

- [Mapping](./mapping.md) - For simple key-value data sets
- [Pipeline](./pipelines.md) - Where lists are commonly referenced
- [Context](./context.md) - For defining variables used in list sources

## See Also

- [Directive Overview](../directives.md)
- [Data Sources](../data_source/reader.md)
- [Key Mapping Action](../actions/key_mapping_action.md)
- [Statement Action](../actions/statement_action.md)

---

*Last updated: 2024-12-19*
*Category: reference*
*Directive Type: data-source*
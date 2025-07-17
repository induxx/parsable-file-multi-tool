# Converters Directive

## Overview

The converters directive transforms unstructured, non-normalized data into structured, normalized formats and vice versa. This directive is particularly valuable when working with complex API data that is difficult to manipulate directly with standard actions, enabling seamless data transformation between different formats and systems.

## Syntax

```yaml
converter:
  name: 'converter_type/format'
  options:
    option_name: value
    helper_list: list_name
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| name | string | Yes | - | Converter type identifier (e.g., 'akeneo/product/csv') |
| options | object | No | {} | Configuration options specific to the converter |

### Configuration Details

#### name
- Specifies the converter type and format
- Format: `system/entity/format` (e.g., 'akeneo/product/api', 'akeneo/product/csv')
- Must match available converter implementations
- Case-sensitive identifier

#### options
- Converter-specific configuration parameters
- Can include helper lists, formatting options, or transformation rules
- Structure varies by converter type

## Examples

### Basic API to CSV Conversion

```yaml
# Convert API data to CSV format
pipeline:
  input:
    http:
      type: rest_api
      account: '%akeneo_api_account%'
      endpoint: products
      method: GET
      converter: 'akeneo/product/api'
  output:
    writer:
      type: buffer_csv
      filename: 'akeneo_products.csv'
      converter: 'akeneo/product/csv'
```

### CSV to API Conversion with Helpers

```yaml
# Define helper data
sources:
  - '%workpath%/akeneo_product.csv'
  - '%workpath%/akeneo_attributes.csv'

list:
  - name: attribute_types
    source: akeneo_attributes.csv
    source_command: key_value_pair
    options:
      key: code
      value: type

# Configure converter with helper data
converter:
  name: 'akeneo/product/csv'
  options:
    attribute_types:list: attribute_types

pipeline:
  input:
    reader:
      type: csv
      filename: 'akeneo_product.csv'
      converter: 'akeneo/product/csv'
  output:
    http:
      type: rest_api
      account: '%akeneo_api_account%'
      endpoint: products
      method: MULTI_PATCH
      converter: 'akeneo/product/api'
```

### Converter with Assistance

```yaml
# Using specialized converter for data correction
converter:
  name: 'flat/akeneo/product/csv'
  options:
    attribute_types:list: attribute_types

pipeline:
  input:
    reader:
      type: csv
      filename: 'raw_product_data.csv'
      converter: 'flat/akeneo/product/csv'
```

## Use Cases

### Use Case 1: API Data Normalization
Transform complex, multi-dimensional API responses into flat, structured data for easier processing.

### Use Case 2: Format Translation
Convert data between different formats (API ↔ CSV, JSON ↔ XML) while preserving data integrity.

### Use Case 3: Data Validation and Correction
Use specialized converters to validate and correct data according to specific system requirements.

## Behavior and Processing

### Processing Order
Converters are applied during data input/output operations, before or after action processing.

### Data Flow
1. **Convert Phase**: Transform input data to normalized structure
2. **Action Phase**: Apply transformation actions to normalized data
3. **Revert Phase**: Transform back to target format if needed

### Converter Methods
- **convert()**: Transforms unstructured data to normalized format
- **revert()**: Transforms normalized data back to original structure

## Common Patterns

### Pattern 1: Bidirectional Conversion
```yaml
# API → CSV → Processing → API
input_converter: 'akeneo/product/api'
# ... processing actions ...
output_converter: 'akeneo/product/api'
```

### Pattern 2: Converter Linking
```yaml
# Link compatible converters for seamless transformation
converter_a: 'system_a/entity/format'
converter_b: 'system_b/entity/format'  # Compatible structure
```

## Converter Types

### Built-in Converters

#### Akeneo Converters
- `akeneo/product/api` - Akeneo API format
- `akeneo/product/csv` - Akeneo CSV format
- `flat/akeneo/product/csv` - Enhanced CSV with validation

#### Generic Converters
- Custom converters for specific data sources
- Format-specific converters (JSON, XML, etc.)

## Common Issues and Solutions

### Issue: Converter Not Found

**Symptoms:** Error messages about unknown converter types.

**Cause:** Specified converter name doesn't match available implementations.

**Solution:** Verify converter name and ensure it's properly installed.

```yaml
# Correct converter specification
converter:
  name: 'akeneo/product/csv'  # Must match exactly
```

### Issue: Missing Helper Data

**Symptoms:** Conversion errors or incomplete data transformation.

**Cause:** Required helper lists or options not provided.

**Solution:** Ensure all required helper data is defined.

```yaml
# Complete converter configuration
list:
  - name: attribute_types
    source: attributes.csv
    source_command: key_value_pair
    options:
      key: code
      value: type

converter:
  name: 'akeneo/product/csv'
  options:
    attribute_types:list: attribute_types  # Required helper
```

## Best Practices

- Always convert API data to flat structured formats like CSV for easier processing
- Use converters for format transformation, not data manipulation (use actions for that)
- Create clean, normalized CSV files before converting to API calls
- Provide necessary helper data for complex attribute types
- Test converter chains thoroughly to ensure data integrity
- Document custom converter requirements and limitations

## Advanced Features

### Converter Linking
Connect compatible converters to enable seamless data transformation between different systems without data loss.

### Custom Converters
Extend the system with custom converters for specific data sources or formats (Work in Progress).

## Related Directives

- [Pipeline](./pipelines.md) - Where converters are commonly used
- [List](./list.md) - For providing helper data to converters
- [Sources](../data_source/reader.md) - Input data configuration

## See Also

- [Directive Overview](../directives.md)
- [Data Transformation Guide](../user-guide/transformations.md)
- [API Integration](../user-guide/transformations.md)
- [Akeneo Product Converter](../converters/akeneo_product_converter.md)

---

*Last updated: 2024-12-19*
*Category: reference*
*Directive Type: processing*
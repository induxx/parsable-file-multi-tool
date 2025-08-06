# Akeneo Product Converter

## Overview

The Akeneo Product Converter transforms Akeneo API product data into structured, flattened formats for easier processing and integration. This converter handles the complex, multi-dimensional structure of Akeneo product data and converts it into normalized formats suitable for downstream actions and transformations.

## Syntax

```yaml
converter:
  name: 'akeneo/product/api'
  options:
    attribute_types:list: list_name
    # Additional converter-specific options
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| name | string | Yes | - | Converter identifier ('akeneo/product/api' or 'akeneo/product/csv') |
| options | object | No | {} | Configuration options for the converter |
| attribute_types:list | string | No | - | Reference to list containing attribute type mappings |

## Examples

### Basic API to Structured Data Conversion

```yaml
# Convert Akeneo API response to structured format
pipeline:
  input:
    http:
      type: rest_api
      account: '%akeneo_connection%'
      endpoint: products
      method: GET
      converter: 'akeneo/product/api'
  actions:
    retain_fields:
      action: retain
      keys: [identifier, family, enabled, values]
  output:
    writer:
      type: csv
      filename: 'akeneo_products.csv'
```

### Converter with Attribute Type Mapping

```yaml
# Define attribute types for proper conversion
list:
  - name: attribute_types
    source: akeneo_attributes.csv
    source_command: key_value_pair
    options:
      key: code
      value: type

# Use converter with attribute type information
converter:
  name: 'akeneo/product/api'
  options:
    attribute_types:list: attribute_types

pipeline:
  input:
    http:
      type: rest_api
      account: '%akeneo_connection%'
      endpoint: products
      converter: 'akeneo/product/api'
  output:
    writer:
      type: csv
      filename: 'converted_products.csv'
      converter: 'akeneo/product/csv'
```

### Bidirectional Conversion (API ↔ CSV)

```yaml
# Convert from CSV back to API format
pipeline:
  input:
    reader:
      type: csv
      filename: 'product_updates.csv'
      converter: 'akeneo/product/csv'
  actions:
    validate_data:
      action: statement
      when:
        field: identifier
        operator: NOT_EMPTY
  output:
    http:
      type: rest_api
      account: '%akeneo_connection%'
      endpoint: products
      method: PATCH
      converter: 'akeneo/product/api'
```

## Converter Types

### akeneo/product/api
- **Purpose**: Converts Akeneo API JSON responses to structured data
- **Input**: Complex Akeneo API product objects
- **Output**: Flattened, normalized data structure
- **Use Case**: Processing API responses for further transformation

### akeneo/product/csv
- **Purpose**: Converts between CSV format and Akeneo API structure
- **Input**: CSV data or structured arrays
- **Output**: Akeneo API-compatible format or CSV structure
- **Use Case**: Importing/exporting product data via CSV files

## Data Structure Transformation

### Input Structure (Akeneo API)
```json
{
  "identifier": "product_001",
  "family": "electronics",
  "enabled": true,
  "values": {
    "name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Product Name"
      }
    ],
    "price": [
      {
        "locale": null,
        "scope": "ecommerce",
        "data": [
          {
            "amount": "99.99",
            "currency": "USD"
          }
        ]
      }
    ]
  }
}
```

### Output Structure (Flattened)
```csv
identifier,family,enabled,name-en_US,price-ecommerce-USD
product_001,electronics,1,Product Name,99.99
```

## Use Cases

### Use Case 1: API Data Import
Import product data from Akeneo PIM system and convert to flat structure for processing.

### Use Case 2: Data Export Preparation
Convert processed data back to Akeneo API format for pushing updates to the PIM system.

### Use Case 3: Format Translation
Transform data between different representations (API JSON ↔ CSV) while preserving data integrity.

## Common Issues and Solutions

### Issue: Missing Attribute Types

**Symptoms:** Incorrect data type handling or conversion errors.

**Cause:** Attribute type information not provided to converter.

**Solution:** Define attribute types list with proper mappings.

```yaml
list:
  - name: attribute_types
    source: akeneo_attributes.csv
    source_command: key_value_pair
    options:
      key: code
      value: type

converter:
  name: 'akeneo/product/api'
  options:
    attribute_types:list: attribute_types
```

### Issue: Locale/Scope Handling

**Symptoms:** Data loss or incorrect attribute value extraction.

**Cause:** Complex locale and scope combinations in Akeneo data.

**Solution:** Ensure converter has access to complete attribute configuration.

### Issue: Large Dataset Performance

**Symptoms:** Slow conversion or memory issues with large product catalogs.

**Cause:** Processing large amounts of complex nested data.

**Solution:** Use streaming processing and consider batch operations.

## Best Practices

- Always provide attribute type mappings for accurate conversion
- Test conversion with sample data before processing large datasets
- Use appropriate converter direction (api vs csv) based on data flow
- Validate converted data structure before downstream processing
- Consider performance implications for large product catalogs
- Document custom attribute handling requirements

## Integration Guidelines

### With Pipeline Actions
- Use converters at input/output boundaries, not within action chains
- Apply data transformations after conversion to structured format
- Validate data integrity after conversion steps

### With External Systems
- Ensure API credentials and connections are properly configured
- Handle rate limiting and error responses from Akeneo API
- Implement proper error handling for conversion failures

## Related Components

- [Converters Directive](../directives/converters.md) - Configuration and usage patterns
- [Pipeline Directive](../directives/pipelines.md) - Integration with data processing workflows
- [List Directive](../directives/list.md) - Attribute type mapping configuration

## See Also

- [Data Transformation Guide](../../../user-guide/transformations.md)
- [API Integration](../../../user-guide/transformations.md)
- [Akeneo PIM Documentation](https://api.akeneo.com/)

---

*Category: reference*
*Component Type: converter*
*Source File: src/Component/Converter/Akeneo/Api/Product.php*

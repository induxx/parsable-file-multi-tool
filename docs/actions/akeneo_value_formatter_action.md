
# Akeneo Value Formatter Action

## Overview

The akeneo_value_format action allows you to format Akeneo-specific attribute values according to their attribute types. It's designed specifically for Akeneo PIM data transformation workflows where you need to convert raw attribute values into formatted, human-readable representations.

## Syntax

```yaml
actions:
  - action: akeneo_value_format
    fields: [field1, field2]
    context:
      pim_catalog_boolean:
        label:
          Y: 'TRUE'
          N: 'FALSE'
      pim_catalog_metric:
        format: '%amount% %unit%'
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| fields | array | Yes | - | Array of field names to format |
| context | object | Yes | - | Formatting configuration for different Akeneo attribute types |

### Parameter Details

#### fields
An array containing the names of fields to be formatted according to their Akeneo attribute types.

- **Format:** Array of strings
- **Example:** `["enabled", "weight", "color"]`
- **Behavior:** All specified fields will be formatted based on their attribute type configuration

#### context
Configuration object that defines how different Akeneo attribute types should be formatted.

- **Format:** Object with attribute type configurations
- **Example:** `{"pim_catalog_boolean": {"label": {"Y": "TRUE", "N": "FALSE"}}}`
- **Behavior:** Provides formatting rules for each supported attribute type

## Examples

### Boolean and Metric Formatting

```yaml
actions:
  - action: akeneo_value_format
    fields: ['enabled', 'weight']
    context:
      pim_catalog_boolean:
        label:
          Y: 'TRUE'
          N: 'FALSE'
      pim_catalog_metric:
        format: '%amount% %unit%'
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "enabled": "Y",
  "weight": {"amount": 2.5, "unit": "kg"}
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "enabled": "TRUE",
  "weight": "2.5 kg"
}
```

### Simple Select with External Source

```yaml
source:
  '%workpath%/attribute_options.csv'

actions:
  - action: akeneo_value_format
    fields: ['color']
    context:
      pim_catalog_simpleselect:
        source: attribute_options
        filter:
          attribute: '{attribute-code}'
          code: '{value}'
        return: 'labels-nl_BE'
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "color": "red"
}
```

**Output (assuming attribute_options.csv contains the mapping):**
```json
{
  "product_name": "Widget Pro",
  "color": "Rood"
}
```

### Multi-Select Options Formatting

```yaml
actions:
  - action: akeneo_value_format
    fields: ['features']
    context:
      pim_catalog_multiselect:
        source: feature_options
        filter:
          attribute: '{attribute-code}'
          code: '{value}'
        return: 'labels-en_US'
        separator: ', '
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "features": ["waterproof", "wireless", "portable"]
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "features": "Waterproof, Wireless, Portable"
}
```

## Supported Attribute Types

### pim_catalog_boolean
Formats boolean values with custom labels.

- **Configuration:** `{"label": {"Y": "Yes", "N": "No"}}`
- **Input:** `"Y"` or `"N"`
- **Output:** Corresponding label value

### pim_catalog_metric
Formats metric values with amount and unit.

- **Configuration:** `{"format": "%amount% %unit%"}`
- **Input:** `{"amount": 2.5, "unit": "kg"}`
- **Output:** `"2.5 kg"`

### pim_catalog_simpleselect
Formats single select values using external option sources.

- **Configuration:** Requires source, filter, and return parameters
- **Input:** Option code
- **Output:** Localized label

### pim_catalog_multiselect
Formats multiple select values using external option sources.

- **Configuration:** Similar to simpleselect with optional separator
- **Input:** Array of option codes
- **Output:** Comma-separated localized labels

## Use Cases

### Use Case 1: Product Export Formatting
Format Akeneo product data for export to e-commerce platforms or external systems.

### Use Case 2: Localized Data Display
Convert attribute codes and values into localized, human-readable formats for different markets.

### Use Case 3: Data Integration Preparation
Transform Akeneo-specific data structures into standardized formats for third-party integrations.

## Common Issues and Solutions

### Issue: Attribute Type Not Recognized

**Symptoms:** Field values are not formatted despite being listed in fields array.

**Cause:** The attribute type is not configured in the context or doesn't match Akeneo's attribute type.

**Solution:** Ensure all attribute types used by your fields are configured in the context.

```yaml
# Add configuration for all attribute types
actions:
  - action: akeneo_value_format
    fields: ['my_field']
    context:
      pim_catalog_text:  # Add missing attribute type
        format: '%value%'
```

### Issue: External Source Not Found

**Symptoms:** Simple select or multi-select formatting fails.

**Cause:** The specified source in the context doesn't exist or is not accessible.

**Solution:** Verify that the source is properly defined and accessible.

```yaml
# Ensure source is defined
source:
  '%workpath%/attribute_options.csv'

actions:
  - action: akeneo_value_format
    fields: ['color']
    context:
      pim_catalog_simpleselect:
        source: attribute_options  # Must match source definition
```

### Issue: Placeholder Replacement Not Working

**Symptoms:** Placeholders like `{attribute-code}` and `{value}` are not replaced in filter conditions.

**Cause:** Field name or value doesn't match the expected format for placeholder replacement.

**Solution:** Verify that field names and values are compatible with placeholder replacement logic.

```yaml
# Ensure field names work with placeholders
actions:
  - action: akeneo_value_format
    fields: ['color']  # Field name becomes {attribute-code}
    context:
      pim_catalog_simpleselect:
        filter:
          attribute: 'color'  # Should match field name
          code: '{value}'     # Will be replaced with field value
```

## Performance Considerations

- External source lookups may impact performance for large datasets
- Consider caching frequently accessed attribute options
- Metric formatting is generally fast
- Complex multi-select formatting may be slower than simple types

## Related Actions

- [Value Mapping in List Action](./value_mapping_in_list_action.md) - Alternative for simple value mapping
- [Format Action](./format_action.md) - General-purpose formatting
- [Statement Action](./statement_action.md) - Add conditional logic around Akeneo formatting

## See Also

- [Akeneo Integration Guide](../user-guide/akeneo-integration.md)
- [Attribute Type Reference](../reference/akeneo-attribute-types.md)
- [Transformation Steps](../directives/transformation_steps.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: formatting*
